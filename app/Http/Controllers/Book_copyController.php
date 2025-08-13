<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [SJH]
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination;
use App\Models\Book; // use the model that we defined [SJH]
use App\Models\Book_copy; // use the model that we defined [SJH]
use App\Models\Rental; // use the model that we defined [SJH]
use DB; // instead of Eloquent, use DB

require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root')."/app/Libraries/book.php");
require_once(config('app.root')."/app/Libraries/book_copy.php");


class Book_copyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $search_key=$request->input('search_key');
        $code_c_rstatus=$request->input('code_c_rstatus');
        $search_condition=array();
        $search_condition []=['inst','=',$_SESSION['lib_inst']];

        if($code_c_rstatus) $search_condition[]=['c_rstatus',$code_c_rstatus];
        if($search_key){
            $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])->orderBy('id','asc')
            ->where($search_condition)
            ->where(function ($query) use ($search_key) {
                $query->where('barcode','=',$search_key);
                $query->orWhere('call_no','=',$search_key);
            })->paginate(10);
        }
        else {
            $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])->orderBy('id','asc')
            ->where($search_condition)->paginate(10);
        }

        return view('book_copy.list')->with('book_copy',$book_copy)->with('search_key',$search_key)->with('code_c_rstatus',$code_c_rstatus);
    }

    /**
     * Show the form for creating a new resource.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!Auth::check() || !Auth::user()->isAdmin()) 
            return redirect('/');
        else {
            $book=Book::where('inst',$_SESSION['lib_inst'])
                ->where('id',request()->route('book'))->first();

            return view('book_copy.create')->with('book',$book);
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
            'call_no' => 'required'
       ]); 
        
     // Check if the book copy with the same call_no or/and barcode exist
        if($request->input('barcode')){ // barcode is not required
            $tmp_bc=Book_copy::where([['inst',$_SESSION['lib_inst']],['call_no',$request->input('call_no')]])
                ->orWhere([['inst',$_SESSION['lib_inst']],['barcode',$request->input('barcode')]])->first();
        }
        else {
            $tmp_bc=Book_copy::where('inst',$_SESSION['lib_inst'])
                ->where('call_no',$request->input('call_no'))->first();
        }

        if(isset($tmp_bc->id)){  // already such book_copy exists
            return redirect('/book_copy/create/'.$request->input('bid'))
                ->with('error','Book Copy with the call no and/or barcode already exists')
                ->with('call_no',$request->input('call_no'))
                ->with('barcode',$request->input('barcode'))
                ->with('location',$request->input('location'))
                ->with('c_rstatus',$request->input('c_rstatus'))
                ->with('comment',$request->input('comment'));
        }

       
     // Create Book Copy
       $bc = new Book_copy;
       $bc->inst=$_SESSION['lib_inst'];
       $bc->id=Book_copy::max('id')+1;
       $bc->bid=$request->input('bid');
       $bc->call_no=$request->input('call_no');
       $bc->barcode=$request->input('barcode');
       $bc->location=$request->input('location');
       $bc->c_rstatus=$request->input('c_rstatus');
       $bc->comment=$request->input('comment');

       $bc->save();
       return redirect('/book/'.$request->input('bid').'/edit')->with('success','Book Copy Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!Auth::check() || !Auth::user()->isAdmin()) // illegal access
            return redirect('/')->with('warning',__("No Authority"));

        $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])
            ->where('id',$id)->first();        
        
        $book=Book::where('inst',$_SESSION['lib_inst'])
            ->where('id',$book_copy->bid)->first();
        
        $rentals=Rental::where('inst',$_SESSION['lib_inst'])
            ->where('bcid',$book_copy->id)->paginate(10);  

        return view('book_copy.edit')->with('book',$book)->with('book_copy',$book_copy)->with('rentals',$rentals);
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
            'call_no' => 'required'
            ]); 
    
      // Find Book copy
        $bc = Book_copy::where('inst',$_SESSION['lib_inst'])
            ->where('id',$id)->first();
        
      // Check if the book copy with the same call_no or/and barcode exist
        if($request->input('barcode')){ // barcode is not required
            $tmp_bc=Book_copy::where('inst',$_SESSION['lib_inst'])
                ->where([['call_no',$request->input('call_no')],['id','!=',$bc->id]])
                ->orWhere([['barcode',$request->input('barcode')],['id','!=',$bc->id]])
                ->first();
        }
        else {
            $tmp_bc=Book_copy::where('inst',$_SESSION['lib_inst'])
                ->Where('call_no',$request->input('call_no'))
                ->where('id','!=',$bc->id)
                ->first();
        }

        if(isset($tmp_bc->id)){  // already such book_copy exists
            return redirect('/book_copy/'.$bc->id.'/edit')
                ->with('error',__("Book Copy with the call no and/or barcode already exists"));
        }

        $bc->call_no=$request->input('call_no');
        $bc->barcode=$request->input('barcode');
        $bc->location=$request->input('location');
        $bc->c_rstatus=$request->input('c_rstatus');
        $bc->comment=$request->input('comment');
    
        $bc->save();
        return redirect('/book_copy/'.$id.'/edit')->with('success',__("Book Copy Updated"));
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

        $bc = Book_copy::where("inst",$_SESSION['lib_inst'])->where("id",$id)->first();

        $bc->delete();
        return redirect('/book/'.$bc->bid.'/edit')->with('success',__("Book Copy removed"));
    }
}