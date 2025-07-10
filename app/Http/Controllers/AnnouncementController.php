<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination;
use App\Announcement;

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(! Auth::check()) { // single-inst mode       
            if(config('app.multi_inst','')) { // multi-inst mode
                if(!isset($_SESSION['lib_inst']) || !$_SESSION['lib_inst']) {                    
                    return view('auth.inst'); // multi-inst mode should start from institution
                }
            }
            else { // if multi-institution is not enabled, use the default institution
                $_SESSION['lib_inst']=config('app.inst',config('app.inst',1));
            }
        }  
        
        $ann=Announcement::where('inst',$_SESSION['lib_inst'])->orderBy('top_yn','desc')
            ->paginate(10);

        return view('announcement.list')->with('ann',$ann);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!Auth::check() || !Auth::user()->isAdmin()) 
            return redirect('/');
        else {
            return view('announcement.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $this->validate($request,[
            'title' => 'required'
       ]); 
        
     // Create Announcement
        $ann = new Announcement;        
        $ann->inst=$_SESSION['lib_inst'];
        $ann->id=Announcement::max('id')+1;
        $ann->title=$request->input('title');
        $ann->body=$request->input('body');
        $ann->top_yn=$request->input('top_yn');
        $ann->create_id=Auth::user()->id;
        $ann->ctime=date('Y-m-d H:i:s');        

        if($ann->save()) { // success
            return redirect('/announcement/')->with('success',__("Announcement Created"));
        }
        else { // error
            return redirect('/announcement/')->with('error',__("Announcement NOT created"));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $ann=Announcement::where('inst',$_SESSION['lib_inst'])
                ->where('id',$id)->first();       
        
        return view('announcement.show')->with('ann',$ann);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!Auth::check()) {
            return redirect()->guest('login'); // for login => intended URI
        }
        else if(!Auth::user()->isAdmin()){ // illegal access           
            return redirect('/')->with('warning',__("No Authority"));
        }

        $ann=Announcement::where('inst',$_SESSION['lib_inst'])
                ->where('id',$id)->first();       
        
        return view('announcement.edit')->with('ann',$ann);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,[            
            ]); 
    
      // Find Rental
        if(!Auth::check() || !Auth::user()->isAdmin()) // illegal access
            return redirect('/')->with('warning',__("No Authority"));

        $ann=Announcement::where('inst',$_SESSION['lib_inst'])->where('id',$id)->first();       
   
        $ann->title=$request->input('title');
        $ann->top_yn=$request->input('top_yn');
        $ann->body=$request->input('body');
        $ann->mod_id=Auth::user()->id;
        $ann->mtime=date('Y-m-d H:i:s');      

        if($ann->save()) { 
            return redirect('/announcement')->with('success',__("Announcement Updated"));
        }
        else { // error
            return redirect('/announcement')->with('error',__("Announcement was NOT updated"));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Auth::check() || !Auth::user()->isAdmin()) // illegal access
            return redirect('/')->with('warning',__("No Authority"));

        $ann = Announcement::where("inst",$_SESSION['lib_inst'])->where("id",$id)->get()->first();
        $ann->delete();
        return redirect('/announcement')->with('success',__("Announcement Removed"));
    }
}
