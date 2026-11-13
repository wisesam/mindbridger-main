<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Pagination;
use App\Models\User;
use App\Models\Rental;

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit($id) // User $user => $id [SJH]
    {   
        $theInst=new \vwmldbm\code\Inst_var($_SESSION['lib_inst']);
        $user = User::where('inst',$_SESSION['lib_inst'])->where('id',$id)->firstOrFail();
        if (Auth::user()->id != $user->id && !Auth::user()->isAdmin()) {
            return redirect('/');
        }
        if (Auth::user()->id != $user->id && $user->utype === 'SA' && !Auth::user()->isAdmin('SA')) {
            abort(403);
        }
        return view('users.edit', compact('user'))->with('theInst', $theInst);
    }

    public function update(Request $request, $id) // User $user => $id [SJH]
    {
        $user = User::where('inst', $_SESSION['lib_inst'])
            ->where('id', $id)
            ->firstOrFail();

        $isSelf = Auth::user()->id == $user->id;
        $isAdmin = Auth::user()->isAdmin();

        // A member may edit only their own profile. Administrators may edit others.
        abort_unless($isSelf || $isAdmin, 403);

        // Only a super admin can reset or update another super admin's account.
        if (!$isSelf && $user->utype === 'SA' && !Auth::user()->isAdmin('SA')) {
            abort(403);
        }

        $changingPassword = $request->filled('password');
        $changingEmail = $request->input('email') !== $user->email;

        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->email, 'email')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'required_with:password_confirmation'],
        ];

        // A person changing their own password must prove knowledge of the old one.
        // An administrator resetting ANOTHER user's password does not need it.
        if ($changingPassword && $isSelf) {
            $rules['current_password'] = [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail(__('The current password is incorrect.'));
                    }
                },
            ];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Role, code and status are administrative settings, never member-editable.
        if ($isAdmin && !$isSelf) {
            if ($request->filled('utype')) {
                $requestedType = $request->input('utype');
                if (in_array($requestedType, ['SA', 'A', 'M', 'G'], true) &&
                    ($requestedType !== 'SA' || Auth::user()->isAdmin('SA'))) {
                    $user->utype = $requestedType;
                }
            }

            if ($request->filled('ustatus') &&
                in_array($request->input('ustatus'), ['A', 'I', 'S', 'PS', 'T'], true)) {
                $user->ustatus = $request->input('ustatus');
            }

            if ($request->filled('code_c_utype')) {
                $user->code_c_utype = $request->input('code_c_utype');
            }
        }

        if ($changingPassword) {
            $user->password = Hash::make($validated['password']);
        }

        if (!$user->isDirty()) {
            return back()->with('warning', __('User Profile Not Updated'));
        }

        $user->save();

        // Preserve the existing log-out-on-email-change behavior; also require a
        // fresh login after changing your own password.
        if ($isSelf && ($changingEmail || $changingPassword)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with(
                'success',
                __('User Profile Updated') . ' ' . __('Please Log in again!')
            );
        }

        return back()->with('success', __('User Profile Updated'));
    }

    public function list(Request $request) {
        $search_key=$request->input('search_key');
        $utype=$request->input('utype');
        $code_c_utype=$request->input('code_c_utype');
        $search_condition=array();
        $search_condition []=['inst','=',$_SESSION['lib_inst']];

        if(Auth::check() && Auth::user()->isAdmin()) {
            // return view('users.list')->with('ulist',User::where('id','<>',Auth::user()->id)->pluck('id','name')->toArray());
            if($utype) $search_condition[]=['utype',$utype];
            if($code_c_utype) $search_condition[]=['code_c_utype',$code_c_utype];
            if($search_key){
                return view('users.list')->with('ulist',User::select(['inst','id','name','utype','code_c_utype'])
                ->where('id','<>',Auth::user()->id)
                ->where($search_condition)
                ->where(function ($query) use ($search_key,$utype) {
                    $query->where('id','=',$search_key);
                    $query->orWhere('name','like','%'.$search_key.'%');
                })->paginate(10))->with('search_key',$search_key)->with('utype',$utype)->with('code_c_utype',$code_c_utype);
            }
            else {
                return view('users.list')->with('ulist',User::select(['inst','id','name','utype','code_c_utype'])
                ->where('id','<>',Auth::user()->id)
                ->where($search_condition)
                ->paginate(10))->with('utype',$utype)->with('code_c_utype',$code_c_utype);
            }            
        }
        else {
            return redirect('/');
        }
    }

    public function choose_list(Request $request) {
        $search_key=$request->input('search_key');
        $utype=$request->input('utype');
        $code_c_utype=$request->input('code_c_utype');
        $search_condition=array();
        $search_condition []=['inst','=',$_SESSION['lib_inst']];

        if(Auth::check() && Auth::user()->isAdmin()) {
            // return view('users.list')->with('ulist',User::where('id','<>',Auth::user()->id)->pluck('id','name')->toArray());
            if($utype) $search_condition[]=['utype',$utype];
            if($code_c_utype) $search_condition[]=['code_c_utype',$code_c_utype];
            if($search_key){
                return view('users.choose_list')->with('ulist',User::select(['inst','id','name','utype','code_c_utype'])
                ->where('id','<>',Auth::user()->id)
                ->where($search_condition)
                ->where(function ($query) use ($search_key,$utype) {
                    $query->where('id','=',$search_key);
                    $query->orWhere('name','like','%'.$search_key.'%');
                })->paginate(10))->with('search_key',$search_key)->with('utype',$utype)->with('code_c_utype',$code_c_utype);
            }
            else {
                return view('users.choose_list')->with('ulist',User::select(['inst','id','name','utype','code_c_utype'])
                ->where('id','<>',Auth::user()->id)
                ->where($search_condition)
                ->paginate(10))->with('utype',$utype)->with('code_c_utype',$code_c_utype);
            }            
        }
        else {
            return redirect('/');
        }
    }

    public function destroy($id) { // User $user => $id [SJH]
        if(!Auth::user()->isAdmin()) return redirect('/');
        User::where('inst',$_SESSION['lib_inst'])->where('id',$id)->delete();
        return redirect('/users/list')->with('success',__("User Deleted"));
    }
}