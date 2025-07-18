<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class LibSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('lib_inst')) {
            session(['lib_inst' => 1]);
        }
       
        if (!session()->has('specialRedirect')) {
            session(['specialRedirect' =>null]);
        }

        if (!session()->has('specialBookId')) {
            session(['specialBookId' => null]);
        } 
        return $next($request);
    }
}
