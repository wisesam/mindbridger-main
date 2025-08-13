<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [SJH]
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination;
use App\Models\Book; // use the model that we defined [SJH]
use App\Models\Book_copy; // use the model that we defined [SJH]
use DB; // instead of Eloquent, use DB

require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root')."/app/Libraries/book.php");
require_once(config('app.root')."/app/Libraries/book_copy.php");
require_once(config('app.root')."/app/Helpers/bookViewHelpers.php");

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        session()->forget('specialRedirect');  // to prevented unexpected redirection
        session()->forget('specialBookId');  // to prevented unexpected redirection

        if(session()->has('inst_uname')){
            $inst_uname=session('inst_uname');
            $theInst= new \vwmldbm\code\Inst_var(null,$inst_uname);
            session(['lib_inst' => $theInst->no]);
        }
        else if(! Auth::check()) { // single-inst mode  
            if(config('app.multi_inst','')) { // multi-inst mode
                if(!session()->has('lib_inst')) {                    
                    return view('auth.inst'); // multi-inst mode should start from institution
                }
            }
            else { // if multi-institution is not enabled, use the default institution
                session(['lib_inst' => config('app.inst',config('app.inst',1))]);
            }
        }    

      // Search
        $search_word=$request->input('search_word');
        $search_target=$request->input('search_target');
        // $search_target: i_title, i_author, i_isbn, i_advanced

        if($request->input('page_size'))
            $pageSize=$request->input('page_size');
        else $pageSize=10;

        $warr[]=['inst',session('lib_inst')];
        if(!Auth::check()){
            $warr[]=['hide_from_guest_yn','<>','Y'];
            // $warr[]=['hide_from_guest_yn','=',null, 'or'];
        } 
        else if(!Auth::user()->isAdmin()) {
            $warr[]=['hide_yn','<>','Y'];
            // $warr[]=['hide_yn','=',null,'or'];
        }

        if($search_word) {
            if($search_target=='i_title') {
                $books=Book::where($warr)->where('title','LIKE',"%{$search_word}%")->orderBy('id','desc')->paginate($pageSize);
            }
            else if($search_target=='i_author') {
                $books=Book::where($warr)->where('author','LIKE',"%{$search_word}%")->orderBy('id','desc')->paginate($pageSize);
            }
            else if($search_target=='i_publisher') {
                $books=Book::where($warr)->where('publisher','LIKE',"%{$search_word}%")->orderBy('id','desc')->paginate($pageSize);
            }
            else if($search_target=='i_isbn') {
                $books=Book::where($warr)->where('isbn','LIKE',"%{$search_word}%")->orderBy('id','desc')->paginate($pageSize);
            }
            else if($search_target=='i_eisbn') {
                $books=Book::where($warr)->where('eisbn','LIKE',"%{$search_word}%")->orderBy('id','desc')->paginate($pageSize);
            }
            else {
                $warr[]=['title','LIKE',"%{$search_word}%"];
                $books=Book::where($warr)
                ->orWhere([['inst',session('lib_inst')],['author','LIKE',"%{$search_word}%"]])
                ->orWhere([['inst',session('lib_inst')],['publisher','LIKE',"%{$search_word}%"]])
                ->orWhere([['inst',session('lib_inst')],['keywords','LIKE',"%{$search_word}%"]])
                ->orWhere([['inst',session('lib_inst')],['isbn','LIKE',"%{$search_word}%"]])
                ->orWhere([['inst',session('lib_inst')],['eisbn','LIKE',"%{$search_word}%"]])
                ->orderBy('id','desc')->paginate($pageSize);
            }
        }
        else {
            if(! Auth::check()) { // exclude hide_yn, hide_from_guest books => well allow the listing only
                $books = Book::where('inst', session('lib_inst'))
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('hide_from_guest_yn', '<>', 'Y')
                            ->orWhereNull('hide_from_guest_yn');
                    })
                    ->where(function ($sub) {
                        $sub->where('hide_yn', '<>', 'Y')
                            ->orWhereNull('hide_yn');
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate($pageSize);
            }               
            else { // logged in, then exclude hide_yn for non admins
                if(!Auth::user()->isAdmin()) {
                    $books = Book::where('inst', session('lib_inst'))
                        ->where(function ($q) {
                            $q->where('hide_yn', '<>', 'Y')
                            ->orWhereNull('hide_yn');
                        })
                        ->orderBy('id', 'desc')
                        ->paginate($pageSize);
                }
                else {
                    $books=Book::where('inst',session('lib_inst'))
                    ->orderBy('id','desc')->paginate($pageSize);
                }
            }
        }

        // $books=Book::orderBy('id','desc')->get();
        return view('book.list')->with('books',$books)->with('inst',session('lib_inst'))
            ->with('search_word',$search_word)
            ->with('search_target',$search_target)->with('request',$request);
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
            return view('book.create');
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
        $isbn_error=false;

        $this->validate($request,[
            'title' => 'required',           
            'cover_image'=>'image|nullable|max:1999'
       ]); 
        
       // Handle File Upload
       if($request->input('file_name')){
            // Get file extension
            $filearray2 = explode(".", $_POST["file_name"]);
            $fext = strtolower($filearray2[sizeof($filearray2)-1]);
            if ($fext != "jpg" && $fext != "jpeg" && $fext != "png"){
                return redirect('/book/'.$book->id.'/edit')->with('error',__("JPG or PNG only!"));
            }
            
			
			// check if the cover image directory by the inst no was created (especially in case of multi-inst mode)
			if(!Storage::exists('public/cover_images/'.session('lib_inst'))) {
				Storage::makeDirectory('public/cover_images/'.session('lib_inst'));
			}
		
            // Get filename
            $filename=strtolower($filearray2[sizeof($filearray2)-2]);
            
            // Filename to store
            $fileNameToStore=$filename.'_'.time().'.'.$fext;
        
            // Upload Image
            $path=config('filesystems.disks.public')['root'].'/cover_images/'.session('lib_inst').'/'.$fileNameToStore;
            $based64Image=substr($request->input('wise_photo_data'), strpos($request->input('wise_photo_data'), ',')+1);
            
            $base64_decoded_string = base64_decode($based64Image);
            if (!$base64_decoded_string) {
                return redirect('/book/create')->with('book',$request->all())->with('error',__("Image Error"));
            }

            $image = imagecreatefromstring($base64_decoded_string);
        
            if($fext=='jpg' || $fext=='jpeg')  imagejpeg($image, $path);
            else if($fext=='png')  imagepng($image, $path);
        }  else {
            $fileNameToStore='noimage.jpg';
            $fileNameToStore=null;
        }


        if($request->input('isbn')) {
            $search_book = Book::where('inst',session('lib_inst'))
                ->where('isbn',$request->input('isbn'))
                ->first();
            if(isset($search_book->id) && $search_book->id!='') {
                $isbn_error=true;
            }
        }

        if($request->input('eisbn')) {
            $search_book = Book::where('inst',session('lib_inst'))
                ->where('eisbn',$request->input('eisbn'))
                ->first();
            if(isset($search_book->id) && $search_book->id!='') {
                $isbn_error=true;
            }
        }
        
     // Create Book
        $book = new Book;
        $book->title=$request->input('title');
        $book->inst=session('lib_inst');
        $book->id=Book::max('id')+1;
        $book->rid=$this::get_new_rid();
        $book->author=$request->input('author');
        $book->c_rtype=$request->input('c_rtype');
        $book->c_genre=$request->input('c_genre');
        $book->c_lang=$request->input('c_lang');
        $book->publisher=$request->input('publisher');
        
        $pdate=$request->input('pub_date'); 
        if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $pdate)) { // right date format: Y-m-d
            $book->pub_date=$pdate;             
        }
        else  if(preg_match('/^[0-9]{4}$/', $pdate)) { // Y is okay
            $book->pub_date=substr($pdate,0,4)."-01-01";
        }      
        else $book->pub_date=null; // none is okay
        
        $book->isbn=$request->input('isbn');
        $book->eisbn=$request->input('eisbn');
        $book->keywords=$request->input('keywords');
        $book->e_resource_yn=$request->input('e_resource_yn');
        $book->cover_image=$fileNameToStore;
        
        $book->rdonly_pdf_yn=$request->input('rdonly_pdf_yn');
        $book->rdonly_video_yn=$request->input('rdonly_video_yn');
        $book->hide_yn=$request->input('hide_yn');
        $book->hide_from_guest_yn=$request->input('hide_from_guest_yn');
        $book->e_res_af_login_yn=$request->input('e_res_af_login_yn');
        
        $book->desc=$request->input('desc');  
        $book->url=$request->input('url');  
        $book->price=$request->input('price');  
        $book->c_grade=$request->input('c_grade');  
        $book->c_category=$request->input('c_category');  
        $book->c_category2=$request->input('c_category2');

        
        if($isbn_error) return redirect('/book/create')->with('book',$request->all())->with('error',__("Duplicate ISBN/e-ISBN!"));

        $book->save();
        return redirect('/book/'.$book->id.'/edit')->with('success',__("Book Created"));

        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(session()->has('lib_inst')){
            $theInst= new \vwmldbm\code\Inst_var(null,session('lib_inst'));
            // session(['lib_inst' => $theInst->no]);
            //session('lib_inst')=$theInst->inst_uname;
        }
        else if(! Auth::check()) {
            if(config('app.multi_inst','')) { // multi-inst mode
                if(!session()->has('lib_inst')) {                    
                    return view('auth.inst'); // multi-inst mode should start from institution
                }
            }
            else { // if multi-institution is not enabled, use the default institution
                session(['lib_inst' => config('app.inst',config('app.inst',1))]);
            }
        } 
        else if(session()->has('lib_inst')){
            $theInst= new \vwmldbm\code\Inst_var(null,session('lib_inst'));            
            session(['inst_uname' => $theInst->inst_uname]);
        } 

        $book=Book::where('inst',session('lib_inst'))
            ->where('id',$id)->first();

        if(isset($book->files) && $book->files!='') {  // remove slashes
            $book->files=stripslashes($book->files);
        }

        if(!isset($book->id))  return redirect('/book/')->with('warning',__("The resource does not exist!"));

        if($book->hide_from_guest_yn=='Y' && !Auth::check()){                      
            $books=Book::where('inst',session('lib_inst'))
                ->where('hide_from_guest_yn','<>','Y')
                ->orderBy('id','desc')->paginate(10);
            
          // This may not comply with Laravel's philosophy but can't find other solution
            $_SESSION['specialRedirect']=true;
            $_SESSION['specialBookId']=$book->id;

            return redirect()->route('login');

            //return redirect('/book/')->with('books',$books)->with('inst',$inst)->with('warning',__("This resource is not available!"));
        }

        if($book->hide_yn=='Y'){
            if((Auth::check() && !Auth::user()->isAdmin()) || !Auth::check()) {
                $books=Book::where('inst',session('lib_inst'))
                    ->orderBy('id','desc')->paginate(10);    
                
                return redirect('/book/')->with('books',$books)->with('inst',$inst)->with('warning',__("This resource is not available!"));
            }
        }

        $book_copy=Book_copy::where('inst',session('lib_inst'))
            ->where('bid',$book->id)->get()->toArray();        

        return view('book.show')->with('book',$book)->with('book_copy',$book_copy);
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

        $book=Book::where('inst',session('lib_inst'))
                ->where('id',$id)->first();

        $this->man_files($book);
        if(!isset($book->id)) {
            $books=Book::where('inst',session('lib_inst'))
                    ->orderBy('id','desc')->paginate(10);    
            return redirect('/book/')->with('books',$books)->with('warning',__("The resource does not exist!"));
        }

        $book_copy=Book_copy::where('inst',session('lib_inst'))
                        ->where('bid',$book->id)->get()->toArray();        
        
        return view('book.edit')->with('book',$book)->with('book_copy',$book_copy);
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
        // check if the cover image directory by the inst no was created (especially in case of multi-inst mode)
        if(!Storage::exists('public/cover_images/'.session('lib_inst'))) {
            Storage::makeDirectory('public/cover_images/'.session('lib_inst'));
        }

        // check if the e-resource directory by the inst no was created (especially in case of multi-inst mode)
         if(!Storage::exists('ebook/'.session('lib_inst'))) {
            Storage::makeDirectory('ebook/'.session('lib_inst'));
        }

        $isbn_error=false;

        $this->validate($request,[
            'title' => 'required'
            ]); 
        
      // Find Book
       $book = Book::where('inst',session('lib_inst'))
       ->where('id',$id)->first();
 
    // Case 1: Delete cover image submitted but the fileds should be saved.
        if($request->input('del_cover_image')=="DEL") {
            Storage::delete('public/cover_images/'.session('lib_inst').'/'.$book->cover_image);
            
          // Check if ISBN, e-ISBN is duplate
            if($request->input('isbn')) {
                $search_book = Book::where('inst',session('lib_inst'))
                    ->where('isbn',$request->input('isbn'))
                    ->where('id','<>',$id)
                    ->first();
                if(isset($search_book->id) && $search_book->id!=$id) {
                    $isbn_error=true;
                }
                else $book->isbn=$request->input('isbn');   
            }
            else $book->isbn=$request->input('isbn');
/*
            if($request->input('eisbn')) {
                $search_book = Book::where('inst',session('lib_inst'))
                    ->where('eisbn',$request
                    ->where('id','<>',$id)
                    ->input('eisbn'))->first();
                if(isset($search_book->id) && $search_book->id!=$id) {
                    $isbn_error=true;
                }
                else $book->eisbn=$request->input('eisbn');   
            }
            else $book->eisbn=$request->input('eisbn');
*/
            $book->title=$request->input('title');
            $book->author=$request->input('author');
            $book->c_rtype=$request->input('c_rtype');
            $book->c_genre=$request->input('c_genre');
            $book->c_lang=$request->input('c_lang');
            $book->publisher=$request->input('publisher');
                
            $pdate=$request->input('pub_date');           
            if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $pdate)) { // right date format: Y-m-d
                $book->pub_date=$pdate;             
            }
            else  if(preg_match('/^[0-9]{4}$/', $pdate)) { // Y is okay
                $book->pub_date=substr($pdate,0,4)."-01-01";
            }      
            else $book->pub_date=null; // none is okay

            $book->keywords=$request->input('keywords');
            $book->e_resource_yn=$request->input('e_resource_yn');    
            $book->cover_image=null;

            $book->rdonly_pdf_yn=$request->input('rdonly_pdf_yn');
            $book->rdonly_video_yn=$request->input('rdonly_video_yn');
            $book->hide_yn=$request->input('hide_yn');
            $book->hide_from_guest_yn=$request->input('hide_from_guest_yn');
            $book->e_res_af_login_yn=$request->input('e_res_af_login_yn');
            $book->desc=$request->input('desc');  
            $book->url=$request->input('url');  
            $book->price=$request->input('price');  
            $book->c_grade=$request->input('c_grade');  
            $book->c_category=$request->input('c_category');  
            $book->c_category2=$request->input('c_category2');  
            $book->toc=$request->input('toc');  
            $book->auto_toc=$request->input('auto_toc');  

            $book->save();
            if($isbn_error) {
                return redirect('/book/'.$book->id.'/edit')->with('error',__("Duplicate ISBN/e-ISBN!"));
            }
            return redirect('/book/'.$book->id.'/edit')->with('success',__("Book Updated"));
        } // End of Case 1: Delete cover image submitted but the fileds should be saved.
     
    // Case 2: Update book submitted
        // Handle Book cover image File Upload
        if($request->input('file_name')){
            // Get file extension
            $filearray2 = explode(".", $_POST["file_name"]);
            $fext = strtolower($filearray2[sizeof($filearray2)-1]);
            if ($fext != "jpg" && $fext != "jpeg" && $fext != "png"){
                return redirect('/book/'.$book->id.'/edit')->with('error',__("JPG or PNG only!"));
            }
            
            // Get filename
            $filename=strtolower($filearray2[sizeof($filearray2)-2]);
            
            // Filename to store
            $fileNameToStore=$filename.'_'.time().'.'.$fext;

            // Upload Image
            $path=config('filesystems.disks.public')['root'].'/cover_images/'.session('lib_inst').'/'.$fileNameToStore;
            $based64Image=substr($request->input('wise_photo_data'), strpos($request->input('wise_photo_data'), ',')+1);    

            $base64_decoded_string = base64_decode($based64Image);
            if (!$base64_decoded_string) {
                return redirect('/book/create')->with('book',$request->all())->with('error',__("Image Error"));
            }

            $image = imagecreatefromstring($base64_decoded_string);
        
            if($fext=='jpg' || $fext=='jpeg')  imagejpeg($image, $path);
            else if($fext=='png')  {
                if (!imagepng($image, $path)) {
                    $error = error_get_last();
                    die("Error saving image: " . $error['message']);
                }
                // imagepng($image, $path);
            }
        } 
      
     // Case 3: Delete book e-resources.
        if($request->input('operation')=="DEL_FILE") {  
            $wlibrary_book=new \wlibrary\book\Book($book->id);          
            $rfile=$wlibrary_book->get_rfile_name($request->input('del_file'));
            $new_files=$wlibrary_book->get_new_files($request->input('del_file'));
            $new_rfiles=$wlibrary_book->get_new_rfiles($rfile);

            Storage::delete('ebook/'.session('lib_inst').'/'.$book->rid."/".$rfile);

            $wlibrary_book->update('files',$new_files); 
            $wlibrary_book->update('rfiles',$new_rfiles);
            $book->files=$new_files;
            $book->rfiles=$new_rfiles;
        }

        if($request->input('isbn')) {
            $search_book = Book::where('inst',session('lib_inst'))
                ->where('isbn',$request->input('isbn'))
                ->where('id','<>',$id)
                ->first();
            if(isset($search_book->id) && $search_book->id<>$id) {
                $isbn_error=true;
            }
            else $book->isbn=$request->input('isbn');   
        }
        else $book->isbn=$request->input('isbn');

        if($request->input('eisbn')) {
       
            $search_book = Book::where('inst',session('lib_inst'))
                ->where('eisbn',$request->input('eisbn'))
                ->where('id','<>',$id)
                ->first();

            if(isset($search_book->id) && $search_book->id<>$id) {
                $isbn_error=true;
            }
            else $book->eisbn=$request->input('eisbn');   
        }
        else $book->eisbn=$request->input('eisbn');

        $book->title=$request->input('title');
        $book->author=$request->input('author');
        $book->c_rtype=$request->input('c_rtype');
        $book->c_genre=$request->input('c_genre');
        $book->c_lang=$request->input('c_lang');
        $book->publisher=$request->input('publisher');

        $pdate=$request->input('pub_date');   
               
        if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $pdate)) { // right date format: Y-m-d
            $book->pub_date=$pdate;             
        }
        else  if(preg_match('/^[0-9]{4}$/', $pdate)) { // Y is okay
            $book->pub_date=substr($pdate,0,4)."-01-01";
        }      
        else $book->pub_date=null; // none is okay

        $book->keywords=$request->input('keywords');
        $book->e_resource_yn=$request->input('e_resource_yn');
       
        $book->rdonly_pdf_yn=$request->input('rdonly_pdf_yn');
        $book->rdonly_video_yn=$request->input('rdonly_video_yn');
        $book->hide_yn=$request->input('hide_yn');
        $book->hide_from_guest_yn=$request->input('hide_from_guest_yn');
        $book->e_res_af_login_yn=$request->input('e_res_af_login_yn');
        $book->desc=$request->input('desc');    
        $book->url=$request->input('url');
        $book->price=$request->input('price');
        $book->c_grade=$request->input('c_grade');  
        $book->c_category=$request->input('c_category');  
        $book->c_category2=$request->input('c_category2');  
        $book->toc=$request->input('toc');
        $book->auto_toc=$request->input('auto_toc');  
            
        if($request->input('file_name')){
                $book->cover_image=$fileNameToStore;
        }
        $book->save();
        if($isbn_error) {
            return redirect('/book/'.$book->id.'/edit')->with('error',__("Duplicate ISBN/e-ISBN!"));
        }
        return redirect('/book/'.$book->id.'/edit')->with('success',__("Book Updated"));
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

        $inst=session('lib_inst');
        $book = Book::where("inst",$inst)->where("id",$id)->get()[0];
        // $book = Book::find($id); return;
        // $book = new \App\Book(["inst"=>session('lib_inst'),"id"=>$id]); // doesn't work
        // Check for correct user       
        
        // echo "<pre>";
        // print_r($book); return;
		
		$msg=null; // 2025.7.2
		
        if($book->cover_image) {
            // Delete Image
            Storage::delete('public/cover_images/'.$inst.'/'.$book->cover_image);
        }
  
        if(Storage::exists('ebook/'.$inst.'/'.$book->rid)) {
            if(!Storage::deleteDirectory('ebook/'.$inst.'/'.$book->rid)){
                $msg=" (Resource Directory Removal Error!)";
            }
        }

        $book->delete();
        return redirect('/book')->with('success','Book Removed'.$msg);
    }

      /**
     * Advanced Search.
     *
     * @param  
     * @return \Illuminate\Http\Response
     */
    public function asearch(Request $request)
    {
        if(! Auth::check()) { // single-inst mode       
            if(config('app.multi_inst','')) { // multi-inst mode
                if(session('lib_inst')==null)
                    return view('auth.inst'); // multi-inst mode should start from institution
            }
            else { // if multi-institution is not enabled, use the default institution
                session(['lib_inst' => config('app.inst',config('app.inst',1))]);
            }
        }  

        $books=null;

        $title=$request->input('title');
        $author=$request->input('author');
        $publisher=$request->input('publisher');
        $isbn=$request->input('isbn');
        $eisbn=$request->input('eisbn');
        $keywords=$request->input('keywords');
        $pub_date1=$request->input('pub_date1');
        $pub_date2=$request->input('pub_date2');
        
        $c_rtype=$request->input('c_rtype'); 
        if(!$c_rtype) $c_rtype=array();

        $c_genre=$request->input('c_genre'); 
        if(!$c_genre) $c_genre=array();

        $c_grade=$request->input('c_grade'); 
        if(!$c_grade) $c_grade=array();

        $c_category=$request->input('c_category'); 
        if(!$c_category) $c_category=array();

        $c_category2=$request->input('c_category2'); 
        if(!$c_category2) $c_category2=array();

        $c_lang=$request->input('c_lang');

        $e_resource_yn=$request->input('e_resource_yn');

        if($c_rtype!==null || $c_genre!==null) { 
            $search_condition=array();
            $search_condition[]=['inst',session('lib_inst')];
            if($title) $search_condition[]=['title','LIKE',"%$title%"];
            if($author) $search_condition[]=['author','LIKE',"%$author%"];
            if($publisher) $search_condition[]=['publisher','LIKE',"%$publisher%"];
            if($isbn) $search_condition[]=['isbn','LIKE',"%$isbn%"];
            if($eisbn) $search_condition[]=['eisbn','LIKE',"%$eisbn%"];
            if($keywords) $search_condition[]=['keywords','LIKE',"%$keywords%"];
            if($c_lang) $search_condition[]=['c_lang',$c_lang];
            if($e_resource_yn) $search_condition[]=['e_resource_yn',$e_resource_yn];
            if($pub_date1 || $pub_date2) {
                if(!$pub_date1) $pub_date1="1600-01-01";
                else if(strlen($pub_date1)==4) { // only year was input
                    $pub_date1.="-01-01";
                }
                if(!$pub_date2) $pub_date2= date("Y-m-d"); 
                else if(strlen($pub_date2)==4) { // only year was input
                    $pub_date2.="-12-31";
                }
                $search_condition[]=['pub_date','>=',$pub_date1];
                $search_condition[]=['pub_date','<=',$pub_date2];
            }
            if(!Auth::check()){
                $search_condition[]=['hide_from_guest_yn','<>','Y'];
            } 
            else if(!Auth::user()->isAdmin()) {
                $search_condition[]=['hide_yn','<>','Y'];
            }

        // This is not the best solution at all (Using Eval) but there seems to be no other way
            $c_rtype_cnt=count($c_rtype);
            $c_genre_cnt=count($c_genre);
            $c_grade_cnt=count($c_grade);
            $c_category_cnt=count($c_category);
            $c_category2_cnt=count($c_category2);
            
            $evalTxt="\$books=\\App\\Book::where(\$search_condition)";

            // if everything is checked, we should not include it in where clause
            // Because the nature is books don't have to have the fields entered
            if($c_rtype_cnt && $c_rtype_cnt<\vwmldbm\code\get_code_stat('code_c_rtype',null,'USE_YN_Y','EN')) { 
                $evalTxt.="->whereIn('c_rtype',\$c_rtype)";
            }

            if($c_genre_cnt && $c_genre_cnt<\vwmldbm\code\get_code_stat('code_c_genre',null,'USE_YN_Y','EN')) { 
                $evalTxt.="->whereIn('c_genre',\$c_genre)";
            }

            if($c_grade_cnt && $c_grade_cnt<\vwmldbm\code\get_code_stat('code_c_grade',null,'USE_YN_Y','EN')) { 
                $evalTxt.="->whereIn('c_grade',\$c_grade)";
            }

            if($c_category_cnt && $c_category_cnt<\vwmldbm\code\get_code_stat('code_c_category',null,'USE_YN_Y','EN')) { 
                $evalTxt.="->whereIn('c_category',\$c_category)";
            }

            if($c_category2_cnt && $c_category2_cnt<\vwmldbm\code\get_code_stat('code_c_category2',null,'USE_YN_Y','EN')) { 
                $evalTxt.="->whereIn('c_category2',\$c_category2)";
            }
            if($request->input('page_size'))
                $pageSize=$request->input('page_size');
            else $pageSize=10;
            $evalTxt.="->orderBy('id','asc')->paginate(".$pageSize.");";  
            
            //$evalTxt.="->orderBy('id','asc')->paginate(5);";

            eval($evalTxt);
            return view('book.asearch')->with('books',$books)->with('operation','ASEARCH')->with('request',$request);
        }
        else return view('book.asearch');
    }

    static function get_new_rid() { // random id for security (eg,for e-resource storage's folder name)
        if(!session()->has('lib_inst')) return; 
        while(true){
            $rid=rand(10000,1000000000); 
            $book = Book::where('inst',session('lib_inst'))
                ->where('rid',$rid)->first();
            if(!isset($book->id)) break;
        }
        return $rid;
    }

    function man_files($book) {
        $files=explode(';',$book->files);
        $rfiles=explode(';',$book->rfiles);
        
        // Sometimes rfiles > files happens
        // then, should remove element of rfiles from the beggining
        // Also the files should be removed from the storage

        $f_diff=count($rfiles)-count($files);

        if($f_diff) { // files mismatch!
            echo "<font color=gray>File mismatch resolving($f_diff)..</font>";

            for($i=0;$i<$f_diff;$i++){                
                if(Storage::exists('ebook/'.session('lib_inst').'/'.$book->rid.'/'.$rfiles[$i])) {                    
                    Storage::delete('ebook/'.session('lib_inst').'/'.$book->rid.'/'.$rfiles[$i]);
                }
                unset($rfiles[$i]);
            }
            echo "<font color=gray> done!</font>";
        }
        $book->rfiles=implode(';',$rfiles);
        $book->save();
    }


        /**
     * Display the specified resource.
     *
     * @param  int  $book_id
     * @return \Illuminate\Http\Response
     */
    public function auto_toc($book_id, Request $request)
    {
        $inst=session('lib_inst');
        $book = Book::where("inst",$inst)->where("id",$book_id)->get()[0];

        // Generate or retrieve ToC
       

        return response()->json([
            'auto_toc' => [
                ['title' => 'Chapter 1', 'page' => 1],
                ['title' => 'Chapter 2', 'page' => 15],
                ['title' => 'Chapter 3', 'page' => 31],
            ],
        ]);
    }
}