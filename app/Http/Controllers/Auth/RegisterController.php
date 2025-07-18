<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth; // [SJH]
use App\Libraries\Code; // [SJH]
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';  // [SJH] Original value: '/home'

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       //[SJH] $this->middleware('guest'); 


    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'utype' => ['required','String'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'inst' =>session('lib_inst'),
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'code_c_utype' => $data['code_c_utype'],
            'utype' => $data['utype'],
            'ustatus' => $data['ustatus'],
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // Don't log in the new user
        // $this->guard()->login($user);

        return $this->registered($request, $user)
                        ?: redirect('/users/list')->with('success', 'User registered successfully!');
    }


    public function showRegistrationForm() { // [SJH]
        if(Auth::check() && Auth::user()->isAdmin()) {
            return view('auth.register');
        }
        else return redirect('/');
    }
}