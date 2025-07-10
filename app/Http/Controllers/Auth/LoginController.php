<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth; // SJH
use Illuminate\Support\Facades\Redirect; // SJH
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

    function logout() { // Added by Sam
        Auth::logout();
        unset($_SESSION['wlibrary_admin']); // Disable unauthorized admin access
        unset($_SESSION['app.root']);  // used in progress_up.php for e-resource upload. Disable unauthorized access
        unset($_SESSION['app.root2']); // used in progress_up.php for e-resource upload. Disable unauthorized access
        unset($_SESSION['app.url']); // used in progress_up.php for e-resource upload. Disable unauthorized access
        unset($_SESSION['lib_inst']); // used for differentiating institutions
        unset($_SESSION['inst_uname']); // used for differentiating institutions
		
        return redirect('/inst');
    }

    function showLoginForm(){            
       // if(config('app.multi_inst')  && !isset($_SESSION['inst_uname'])) {
       if(config('app.multi_inst')  && !isset($_SESSION['lib_inst'])) {
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