<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination;
use App\User;
use App\Rental;

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
        $user = User::where('inst',$_SESSION['lib_inst'])->where('id',$id)->first();
        if(Auth::user()->id !=$user->id && !Auth::user()->isAdmin()) 
            return redirect('/');
        else return view('users.edit', compact('user'))->with('theInst',$theInst);
    }

    public function update($id) // User $user => $id [SJH]
    { 
        $user = User::where('inst',$_SESSION['lib_inst'])->where('id',$id)->first();
        if($user->email == request('email')) { // Email is not being updated
            if(request('password')) {
                $this->validate(request(), [
                    'name' => 'required',
                    'password' => 'min:8|confirmed'
                ]);
            }
            else {
                $this->validate(request(), [
                    'name' => 'required',
                ]);
            }
          
            $user->name = request('name');

            if(request('utype')){
                if(request('utype')=='SA'){ // Super admin only can set other admin as SA
                    if(Auth::user()->isAdmin('SA')){
                        $user->utype = 'SA';
                    }
                }
                else  $user->utype = request('utype');
            }
            
            $user->ustatus = request('ustatus');
            if(request('code_c_utype') && Auth::user()->isAdmin()) $user->code_c_utype = request('code_c_utype');
            if(request('password')) $user->password = bcrypt(request('password'));
            
            $old_aved_at=$user->updated_at;
            $user->save();
            $new_saved_at=$user->updated_at;

            if($old_aved_at==$new_saved_at) 
                return back()->with('warning',__("User Profile Not Updated")); 
            else return back()->with('success',__("User Profile Updated"));           
        }
        else { // Email is being updated
            if(request('password')) {
                $this->validate(request(), [
                    'name' => 'required',
                     //'email' => 'required|email|unique:users',
                    'email' => 'email|required|unique:users',
                    'password' => 'min:8|confirmed'
                ]);
            }
            else {
                $this->validate(request(), [
                    'name' => 'required',
                    'email' => 'email|required|unique:users'
                ]);
            }
           
            $user->name = request('name');
            $user->email = request('email');
            
            if(request('utype')){
                if(request('utype')=='SA'){
                    if(Auth::user()->isAdmin('SA')){
                        $user->utype = 'SA';
                    }
                }
                else  $user->utype = request('utype');
            }
           
            if(request('code_c_utype') && Auth::user()->isAdmin()) $user->code_c_utype = request('code_c_utype');
            
            if(request('password')) $user->password = bcrypt(request('password'));
            
            $old_saved_at=$user->updated_at;
            $user->save();
            $new_saved_at=$user->updated_at;

            if($old_saved_at==$new_saved_at) 
                return back()->with('warning',__("User Profile Not Updated")); 
            
            else {
                if(Auth::user()->id==$user->id) { // User him/herself changed the email, then log out and go to the log in page
                    Auth::logout();
                    return redirect('/login')->with('success',__("User Profile Updated (".$user->email.")").' '.__("Please Log in again!"));
                }
                else {
                    return back()->with('success',__("User Profile Updated"));
                }                
            }
        }
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