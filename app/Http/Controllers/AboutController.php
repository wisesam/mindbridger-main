<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [SJH]
use Illuminate\Support\Facades\DB; // [SJH]
use Illuminate\Support\Facades\Hash; // [SJH]

use App\Models\About; // [SJH]

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

/**
 * Show About
 *
 * @return \Illuminate\Http\Response
 */
class AboutController extends Controller {
    public function about() {
   
        if(config('app.multi_inst','')) { // multi-inst mode                         
            if(!isset($_SESSION['lib_inst']) || !$_SESSION['lib_inst']) {                    
                return view('auth.inst'); // multi-inst mode should start from institution
            }
        }
        else { // if multi-institution is not enabled, use the default institution
            $_SESSION['lib_inst']=config('app.inst',config('app.inst',1));
        }
 
        $about=About::where('inst',$_SESSION['lib_inst'])->first();
        if(!isset($about->inst)) { // about record was not created, so do it now
            $a = new About;
            $a->inst=$_SESSION['lib_inst'];
            $a->about_txt=__("<h2>Welcome to WISE Library System!</h2>");
            $a->save();
            $about=About::where('inst',$_SESSION['lib_inst'])->first();
        }
        return view('about.about')->with('about',$about)->with('aboutPage',true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        if(!Auth::check() || !Auth::user()->isAdmin()) // illegal access
            return redirect('/')->with('warning',__("No Authority"));
        
        $about=About::where('inst',$_SESSION['lib_inst'])->first();
        return view('about.edit')->with('about',$about);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            'email' => 'email'
            ]); 
    
      // Find Rental
        if(!Auth::check() || !Auth::user()->isAdmin()) // illegal access
            return redirect('/')->with('warning',__("No Authority"));

        $about=About::where('inst',$_SESSION['lib_inst'])->first();       
   
        $about->about_txt=$request->input('about_txt');
        $about->header=$request->input('header');
        $about->footer=$request->input('footer');
       
        if($about->save()) { 
            return redirect('/about')->with('success',__("About Information Updated"));
        }
        else { // error
            return redirect('/about')->with('error',__("About Information was NOT updated"));
        }
    }
}