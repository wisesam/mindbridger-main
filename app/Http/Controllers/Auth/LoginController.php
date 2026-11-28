<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            session(['lib_inst' => $u->inst]); // being used?   
            session(['uid' => $u->id]);   
            session(['app.root' => config('app.root')]);  
            session(['app.root2' => config('app.root2')]);  
            session(['app.url' => config('app.url')]);  
            
            $_SESSION['app.root'] = config('app.root'); // $_SESSION used for mindbridger code
            $_SESSION['app.root2'] = config('app.root2'); // $_SESSION used for mindbridger code
            $_SESSION['app.url'] = config('app.url'); // $_SESSION used for mindbridger code
            $_SESSION['lib_inst'] = $u->inst; // $_SESSION used for mindbridger code
            $_SESSION['uid'] = $u->id; // $_SESSION used for mindbridger code
            
            if($u->isAdmin()) {
                $_SESSION['wlibrary_admin'] = $u->utype; //SA(Super Admin) or A, $_SESSION used for mindbridger code
            }

            // Return to the page originally requested, not always the home page.
            // Laravel auth middleware stores `url.intended` for protected routes;
            // direct Sign in links can set a safe return_to via showLoginForm().
            $intended = $request->session()->get('url.intended');
            $safeIntended = $this->safeReturnUrl($intended);
            if ($safeIntended === null) {
                $request->session()->forget('url.intended');
            } else {
                $request->session()->put('url.intended', $safeIntended);
            }
            return redirect()->intended('/');
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

    /**
     * Convert a redirect target to a SAME-APPLICATION absolute URL.
     * Laravel Redirector::to() adds the configured installation base path to
     * root-relative paths. Passing /mindbridger/book/... into an app hosted
     * at /mindbridger would therefore generate /mindbridger/mindbridger/book/...
     * An absolute, validated URL avoids this double-prefix problem.
     * Reject off-site destinations, protocol-relative links, encoded slashes,
     * control characters and paths outside a subdirectory installation.
     */
    private function safeReturnUrl($candidate): ?string
    {
        if (!is_string($candidate) || $candidate === '' || strlen($candidate) > 3000
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $candidate)) {
            return null;
        }

        $parts = parse_url($candidate);
        if ($parts === false || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return null;
        }
        $appUrl = (string) config('app.url');
        $appParts = parse_url($appUrl) ?: [];

        // An absolute redirect is safe only when the origin is our application.
        // Include the port: another service on localhost:8080 is not the same app.
        $appScheme = strtolower((string) ($appParts['scheme'] ?? ''));
        $appHost = (string) ($appParts['host'] ?? '');
        if (!in_array($appScheme, ['http', 'https'], true) || $appHost === '') {
            return null;
        }
        $appPort = (int) ($appParts['port'] ?? ($appScheme === 'https' ? 443 : 80));

        if (isset($parts['scheme']) || isset($parts['host'])) {
            // Laravel's auth middleware may store an absolute intended URL.
            $candidateScheme = strtolower((string) ($parts['scheme'] ?? ''));
            $candidateHost = (string) ($parts['host'] ?? '');
            $candidatePort = (int) ($parts['port'] ?? ($candidateScheme === 'https' ? 443 : 80));
            if (!in_array($candidateScheme, ['http', 'https'], true)
                || $candidateScheme !== $appScheme
                || strcasecmp($candidateHost, $appHost) !== 0
                || $candidatePort !== $appPort) {
                return null;
            }
        } elseif ($candidate[0] !== '/' || str_starts_with($candidate, '//')) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        $decodedPath = rawurldecode($path);
        if (!str_starts_with($path, '/') || preg_match('/%(?:2f|5c|0d|0a|00)/i', $path)
            || str_starts_with($decodedPath, '//')
            || str_contains($decodedPath, '\\') || preg_match('/[\x00-\x1F\x7F]/', $decodedPath)
            || preg_match('#(?:^|/)\.\.(?:/|$)#', $decodedPath)) {
            return null;
        }

        $basePath = rtrim((string) ($appParts['path'] ?? ''), '/');
        if ($basePath !== '' && $basePath !== '/'
            && $path !== $basePath && !str_starts_with($path, $basePath . '/')) {
            return null;
        }

        // Prevent a login -> login redirect loop.
        $loginPath = parse_url(route('login'), PHP_URL_PATH);
        if ($path === $loginPath) {
            return null;
        }
        // Always pass Laravel an absolute URL to avoid adding /mindbridger twice.
        // Preserve the query string (book, section, language and resume page).
        $origin = $appScheme . '://' . $appHost
            . (isset($appParts['port']) ? ':' . $appParts['port'] : '');
        return $origin . $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    function showLoginForm(Request $request)
    {
        // 1. Explicit return_to for a voluntary sign-in (bilingual reader, etc.).
        // 2. Laravel intended URL when auth middleware intercepted a request.
        // 3. Same-site Referer for ordinary Sign in links without return_to.
        $returnTo = $request->query('return_to');
        if (is_string($returnTo) && $returnTo !== '') {
            $safePath = $this->safeReturnUrl($returnTo);
            if ($safePath !== null) {
                $request->session()->put('url.intended', $safePath);
            }
        } elseif (!$request->session()->has('url.intended')) {
            $referer = $request->headers->get('referer');
            $safePath = $this->safeReturnUrl($referer);
            if ($safePath !== null) {
                $request->session()->put('url.intended', $safePath);
            }
        }

        if (config('app.multi_inst') && !session()->has('lib_inst')) {
            return redirect('/inst');
        }
        return view('auth.login');
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