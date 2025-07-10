<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth; // [SJH]
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination;
use DB; // instead of Eloquent, use DB
use App\Rental; // use the model that we defined [SJH]
use App\vRental; // use the model that we defined [SJH]
use App\Book; // use the model that we defined [SJH]
use App\Book_copy; // use the model that we defined [SJH]

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class RentalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->guest('login'); // for login => intended URI
        }
        // else if(!Auth::user()->isAdmin()){ // illegal access           
        //     return redirect('/')->with('warning','No Authority');
        // }

        $barcode= $request->input('barcode');
      
        if($barcode) { // search by Barcode
            $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])->where('barcode',$barcode)->first();  
            if(isset($book_copy->id)) {
                if($request->input('mode')=='RENTED') {
                    $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code',null,null," and rented_yn='Y'");
                   
                    $rentals=Rental::where('inst',$_SESSION['lib_inst'])->where('bcid',$book_copy->id )->whereNotNull('id')->orderBy('rent_date','desc')->where('c_rent_status',$rented_code)->paginate(10); 
                }
                else $rentals=Rental::where('inst',$_SESSION['lib_inst'])->where('bcid',$book_copy->id )->whereNotNull('id')->orderBy('rent_date','desc')->paginate(10);  
            }   
            
            if(isset($rentals[0]) ) { // there is one or more rental records of the book copy                       
            
                $book=Book::where('inst',$_SESSION['lib_inst'])
                    ->where('id',$book_copy->bid)->first();
                
                $new_rental_ok=Rental::is_new_rental_ok($book_copy->id);

                return view('rental.list')->with('rentals',$rentals)->with('book',$book)->with('book_copy',$book_copy)->with('barcode',$barcode)->with('new_rental_ok',$new_rental_ok);
            }
            else if((isset($book_copy->id) && $book_copy->id)) { // the book copy exsits by the barcode but there is not rental records    
            
                $book=Book::where('inst',$_SESSION['lib_inst'])
                    ->where('id',$book_copy->bid)->first();
                
                $new_rental_ok=Rental::is_new_rental_ok($book_copy->id);

                return view('rental.list')->with('rentals',$rentals)->with('book',$book)->with('book_copy',$book_copy)->with('barcode',$barcode)->with('new_rental_ok',$new_rental_ok);
        
            }
            else {         
                $rentals=null;
                return view('rental.list')->with('rentals',$rentals);
            }
           
            // $v_rentals=DB::select("select * from ".config('database.connections.mysql.prefix')."bookcopy_rental_view where isnull(id)=false");
            // echo "<pre>"; print_r($v_rentals); return;
            // return view('rental.list')->with('rentals',$v_rentals);
            
        }
        else { // show all records (not seached with barcode)
            if($request->input('mode')=='RENTED') {
                $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code',null,null," and rented_yn='Y'");
                if(Auth::user()->isAdmin()){ // admin 
                    $rentals=Rental::orderBy('id','asc')->where('inst',$_SESSION['lib_inst'])->where('c_rent_status',$rented_code)->orderBy('id','desc')->paginate(10);
                }
                else { // normal user
                    $rentals=Rental::orderBy('id','asc')->where('inst',$_SESSION['lib_inst'])->where('c_rent_status',$rented_code)->where('uid',Auth::user()->id)->orderBy('id','desc')>paginate(10);
                }
               
            }
            else {
                if(Auth::user()->isAdmin()){ // admin 
                    $rentals=Rental::where('inst',$_SESSION['lib_inst'])->orderBy('id','desc')->paginate(10);
                }
                else { // normal user
                    $rentals=Rental::where('inst',$_SESSION['lib_inst'])->where('uid',Auth::user()->id)->orderBy('id','desc')->paginate(10);
                }
            }
            return view('rental.list')->with('rentals',$rentals);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($bcid)
    {
        if(!Auth::check()) {
            return redirect()->guest('login'); // for login => intended URI
        }
        else if(!Auth::user()->isAdmin()){ // illegal access           
            return redirect('/')->with('warning',__("No Authority"));
        }

        $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])
            ->where('id',$bcid)->first(); 
        
        $book=Book::where('inst',$_SESSION['lib_inst'])
            ->where('id',$book_copy->bid)->first();

        $new_rental_ok=Rental::is_new_rental_ok($book_copy->id);

        if(\vwmldbm\code\get_c_name('code_c_rstatus',$book_copy->c_rstatus,'available_yn')=='Y') $available_yn=true;
        else $available_yn=false;

        if($new_rental_ok && $available_yn) return view('rental.create')->with('book_copy',$book_copy)->with('book',$book);
        else return redirect('/')->with('error',__("Illegal Operation!"));
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
            'uid' => 'required',
            'rent_date' => 'required',
            'due_date' => 'required'
       ]); 

        // Create Book Copy
        $r = new Rental;
        $r->inst=$_SESSION['lib_inst'];
        $r->id=Rental::max('id')+1;
        $r->bcid=$request->input('bcid');
        $r->uid=$request->input('uid');
        $r->rent_date=$request->input('rent_date');
        $r->due_date=$request->input('due_date');

        $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code','NO_CODE',null," and rented_yn='Y'");
        $r->c_rent_status=$rented_code;

        $r->rcomment=$request->input('rcomment');

        if($r->save()) { // success            return redirect('/rental/?barcode='.$request->input('barcode'))->with('success','Rental Added');

            $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])
                ->where('barcode',$request->input('barcode'))->first();   
            $code_c_rstatus_rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',$rented_code,'rstatus_code',null,null,null);
            
            $book_copy->c_rstatus=$code_c_rstatus_rented_code;
            $book_copy->save();
            return redirect('/rental')->with('success',__("New Rental registered"));
        }
        else { // error occured
            return redirect('/rental')->with('error',__("Rental registration error"));
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

        $rental=Rental::where('inst',$_SESSION['lib_inst'])
            ->where('id',$id)->first();  

        $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])
            ->where('id',$rental->bcid)->first();        
        
        $book=Book::where('inst',$_SESSION['lib_inst'])
            ->where('id',$book_copy->bid)->first();
        
        return view('rental.edit')->with('rental',$rental)->with('book',$book)->with('book_copy',$book_copy);
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
            'due_date' => 'date_format:Y-m-d H:i:s',
            'return_date' => 'date_format:Y-m-d H:i:s'
            ]); 
    
      // Find Rental
      if(!Auth::check() || !Auth::user()->isAdmin()) // illegal access
            return redirect('/')->with('warning',__("No Authority"));

        $rental=Rental::where('inst',$_SESSION['lib_inst'])
            ->where('id',$id)->first();        
   
        $rental->return_date=$request->input('return_date');
        $rental->c_rent_status=$request->input('c_rent_status');
        $rental->rcomment=$request->input('rcomment');
    
        if($rental->save()) {            
            $code_c_rstatus_changed_code= \vwmldbm\code\get_c_name('code_c_rent_status',$request->input('c_rent_status'),'rstatus_code',null,null,null);
            $book_copy=Book_copy::where('inst',$_SESSION['lib_inst'])
                ->where('barcode',$request->input('barcode'))->first();   
            $book_copy->c_rstatus=$code_c_rstatus_changed_code;
            $book_copy->save();

            return redirect('/rental/'.$id.'/edit')->with('success',__("Rental Information Updated"));
        }
        else { // error

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
        //
    }

    /**
     * List the rented records of user.
     *
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function list($uid)
    {
        $rentals=Rental::where('inst',$_SESSION['lib_inst'])->where('uid',$uid)->orderBy('id','desc')->paginate(10);

        return view('rental.list')->with('rentals',$rentals)->with('uid',$uid);
    }

        /**
     * List the all rental records of user.
     *
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function rented_list($uid)
    {
        $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code',null,null," and rented_yn='Y'");
        $rentals=Rental::orderBy('id','desc')->where('inst',$_SESSION['lib_inst'])->where('uid',$uid)->where('c_rent_status',$rented_code)->paginate(10);

        return view('rental.list')->with('rentals',$rentals)->with('uid',$uid);;
    }

     /**
     * List the rented records of user.
     *
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function list_by_book_copy($bcid)
    {
        $rentals=Rental::where('inst',$_SESSION['lib_inst'])->where('bcid',$bcid)->orderBy('id','desc')->paginate(10);

        return view('rental.list')->with('rentals',$rentals)->with('bcid',$bcid);
    }

        /**
     * List the all rental records of user.
     *
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function rented_list_by_book_copy($bcid)
    {
        $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code',null,null," and rented_yn='Y'");
        $rentals=Rental::orderBy('id','desc')->where('inst',$_SESSION['lib_inst'])->where('bcid',$bcid)->where('c_rent_status',$rented_code)->paginate(10);

        return view('rental.list')->with('rentals',$rentals)->with('bcid',$bcid);;
    }
}