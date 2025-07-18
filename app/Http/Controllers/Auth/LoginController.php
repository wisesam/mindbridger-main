<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth; // SJH
use Illuminate\Support\Facades\Redirect; // SJH
use Illuminate\Support\Facades\Hash; // SJH 
use Illuminate\Http\Request;


require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    function __construct() {       
        $this->middleware('guest')->except('logout');    
    }

    function redirectTo() {
        // if(Auth::check() && Auth::user()->utype==10) {  // Sysadmin
        //     return ('/admin/dashboard');
        // }
        // else return $this->redirectTo;
        return $this->redirectTo;
    }

    function login(Request $request) {
      
        $u = User::where('inst', session('lib_inst'))
         ->where('email', $request->get('email'))
         ->first();

         if ($u && Hash::check($request->get('password'), $u->password)) {
            // Password is correct
            // Login the user manually (optional if not using Laravel auth)
            Auth::login($u); 
            session(['wlibrary_admin' => $u->isAdmin()]); // being used?
            $_SESSION['lib_inst'] = $u->inst; // $_SESSION used for mindbridger code
            if($u->isAdmin()) {
                $_SESSION['wlibrary_admin'] = $u->utype; //SA(Super Admin) or A, $_SESSION used for mindbridger code
            }

            return redirect('/');
        } else { // login fail
            return back()->withErrors(['email' => 'Invalid email or password.']);
        }
    }

    function logout() { // Added by Sam
        Auth::logout();
        session()->forget('wlibrary_admin'); // Disable unauthorized admin access
        session()->forget('app.root');  // used in progress_up.php for e-resource upload. Disable unauthorized access
        session()->forget('app.root2');  // used in progress_up.php for e-resource upload. Disable unauthorized access
        session()->forget('app.url');  // used in progress_up.php for e-resource upload. Disable unauthorized access
        session()->forget('lib_inst');  // used in progress_up.php for e-resource upload. Disable unauthorized access
        session()->forget('inst_uname');  // used in progress_up.php for e-resource upload. Disable unauthorized access
        if(config('app.multi_inst')) return redirect('/inst');
        else return redirect('/');
    }

    function showLoginForm(){            
       // if(config('app.multi_inst')  && !isset($_SESSION['inst_uname'])) {
       if(config('app.multi_inst')  && !session()->has('lib_inst')) {
            return redirect('/inst');
        }
        else return view('auth.login');
    }

    function login_clear() { // Added by Sam
        session_unset();
        session_destroy();
        return redirect('/login');
    }

    /**
     * Get the needed authorization credentials from the request.
     * [SJH] overriding the one in AuthenticatesUsers.php for multi-institution
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('inst',$this->username(), 'password');
    }
}