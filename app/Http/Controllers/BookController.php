<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination;
use App\Models\Book; 
use App\Models\Book_copy; 
use App\Models\BookTextMeta;
use App\Models\User; 
use App\Models\ReadingHistory;
use Illuminate\Support\Facades\DB; // transactions for related database writes

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

        // All branches share a visibility scope. Do not use ungrouped orWhere,
        // which can unintentionally reveal hidden books across institutions.
        $search_word = $request->input('search_word');
        $search_target = $request->input('search_target');
        $pageSize = min(100, max(1, (int) $request->input('page_size', 10)));
        $query = Book::visibleTo(Auth::user());

        if ($search_word !== null && trim((string) $search_word) !== '') {
            $word = '%' . trim((string) $search_word) . '%';
            $field = [
                'i_title' => 'title',
                'i_author' => 'author',
                'i_creator' => 'creator',
                'i_publisher' => 'publisher',
                'i_isbn' => 'isbn',
                'i_eisbn' => 'eisbn',
            ][$search_target] ?? null;

            if ($field) {
                $query->where($field, 'LIKE', $word);
            } else {
                $query->where(function ($q) use ($word) {
                    foreach (['title', 'author', 'creator', 'publisher', 'keywords', 'isbn', 'eisbn'] as $column) {
                        $q->orWhere($column, 'LIKE', $word);
                    }
                });
            }
        }

        $books = $query->orderBy('id', 'desc')->paginate($pageSize)
            ->appends($request->query());

        return view('book.list')->with('books', $books)->with('inst', session('lib_inst'))
            ->with('search_word', $search_word)->with('search_target', $search_target)
            ->with('request', $request)
            ->with('canPublishBooks', Auth::check() && Auth::user()->canPublishBooks());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Auth::check()) return redirect()->guest('login');
        // Central policy: admins or users.utype -> code_c_utype.w2_utype, create_yn = Y.
        abort_unless(Auth::user()->canPublishBooks(), 403, 'You cannot create books.');
        return view('book.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(Auth::check() && Auth::user()->canPublishBooks(), 403);
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
                return redirect('/book/create')->with('error',__("JPG or PNG only!"));
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
        $book->created_by = (string) Auth::user()->id; // never trust form-supplied owner
        $book->id=Book::max('id')+1;
        $book->rid=$this::get_new_rid();
        $book->author=$request->input('author');
        $book->creator=Auth::user()->name; // Derived from authenticated user; never trust form input.
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

        DB::transaction(function () use ($book) {
            $book->save();
        });
        return redirect('/book/'.$book->id.'/edit')->with('success',__("Book Created"));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
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
        else if(session()->has('lib_inst')) {
            $theInst= new \vwmldbm\code\Inst_var(null,session('lib_inst'));            
            session(['inst_uname' => $theInst->inst_uname]);
        } 

		// for pdf file access by guest. [TBR] To be refactored later 
		$_SESSION['inst'] = session('lib_inst');
		$_SESSION['app.root'] = config('app.root');
		$_SESSION['app.root2'] = config('app.root2');
		$_SESSION['app.url'] = config('app.url');
		
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
            if(!Auth::check() || !Auth::user()->canManageBook($book)) {
                $books=Book::where('inst',session('lib_inst'))
                    ->orderBy('id','desc')->paginate(10);    
                
                return redirect('/book/')->with('books',$books)->with('inst',session('lib_inst'))->with('warning',__("This resource is not available!"));
            }
        }
        
        $book_copy=Book_copy::where('inst',session('lib_inst'))
            ->where('bid',$book->id)->get()->toArray();        
        
        // Reading History
        // From Book view normal users click start reading button
        $readingHistory = null;
        if($request->input('reading_action') == "START_READING") {
            $readingHistory = ReadingHistory::firstOrCreate(
                [
                    'inst'    => session('lib_inst'),
                    'user_id' => session('uid'),
                    'book_id' => $book->id,
                ],
                [
                    'status'     => ReadingHistory::STATUS_INPROGRESS, // default
                    'start_time' => now(),
                ]
            );
        } else { 
            $readingHistory= ReadingHistory::where('inst', session('lib_inst'))
            ->where('user_id', session('uid'))
            ->where('book_id', $book->id)
            ->first();
        }
        // End Reading History

        return view('book.show')->with('book',$book)->with('book_copy',$book_copy)->with('readingHistory',$readingHistory);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Auth::check()) return redirect()->guest('login');

        $book = Book::where('inst', session('lib_inst'))
            ->where('id', $id)->firstOrFail();
        abort_unless(Auth::user()->canManageBook($book), 403);

        // Repair only after verifying the user owns this book.
        $this->man_files($book);

        $book_copy=Book_copy::where('inst',session('lib_inst'))
                        ->where('bid',$book->id)->get()->toArray();        
        
        $bookTextMeta=BookTextMeta::where('inst',session('lib_inst'))
            ->where('book_id',$book->id)->first();

        $creatorDisplayName = $book->creator;
        if (blank($creatorDisplayName) && filled($book->created_by)) {
            $creatorDisplayName = User::where('inst', $book->inst)
                ->where('id', $book->created_by)->value('name');
        }

        return view('book.edit')->with('book',$book)->with('book_copy',$book_copy)
            ->with('bookTextMeta',$bookTextMeta)->with('creatorDisplayName',$creatorDisplayName);
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
        abort_unless(Auth::check(), 403);
        $book = Book::where('inst', session('lib_inst'))->where('id', $id)->firstOrFail();
        abort_unless(Auth::user()->canManageBook($book), 403);

        // Existing creator name is immutable. For legacy records missing the name,
        // derive it from the original `created_by` account, never today's editor.
        if (blank($book->creator) && filled($book->created_by)) {
            $book->creator = User::where('inst', $book->inst)
                ->where('id', $book->created_by)->value('name');
        }

        // Ensure duplicate ISBN does not allow mutation before validation errors.
        foreach (['isbn', 'eisbn'] as $column) {
            if ($request->filled($column) && Book::where('inst', session('lib_inst'))
                ->where('id', '<>', $id)->where($column, $request->input($column))->exists()) {
                return redirect('/book/'.$id.'/edit')->with('error', __('Duplicate ISBN/e-ISBN!'));
            }
        }

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
            'title' => 'required',
            ]); 
        
      // Book and ownership already checked above.
 
    // Case 1: Delete cover image submitted but the fileds should be saved.
        if($request->input('del_cover_image')=="DEL") {
            $oldCoverImage = $book->cover_image;

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
            $book->meta_data=$request->input('meta_data');  

            if ($isbn_error) {
                return redirect('/book/'.$book->id.'/edit')->with('error',__("Duplicate ISBN/e-ISBN!"));
            }

            // Commit both database changes together; delete the image only after success.
            DB::transaction(function () use ($book, $request, $id) {
                $book->save();
                BookTextMeta::updateOrCreate(
                    [
                        'inst'    => session('lib_inst'),
                        'book_id' => $id
                    ],
                    [
                        'text' => $request->input('pdf_text'),
                        'meta' => $request->input('meta_info'),
                    ]
                );
            });

            if ($oldCoverImage) {
                Storage::delete('public/cover_images/'.session('lib_inst').'/'.$oldCoverImage);
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
        $book->meta_data=$request->input('meta_data');
        
        if($request->input('file_name')){
                $book->cover_image=$fileNameToStore;
        }
        if ($isbn_error) {
            return redirect('/book/'.$book->id.'/edit')->with('error',__("Duplicate ISBN/e-ISBN!"));
        }

        // Keep Book and BookTextMeta consistent if either database write fails.
        DB::transaction(function () use ($book, $request, $id) {
            $book->save();
            BookTextMeta::updateOrCreate(
                [
                    'inst'    => session('lib_inst'),
                    'book_id' => $id
                ],
                [
                    'text' => $request->input('pdf_text'),
                    'meta' => $request->input('meta_info'),
                ]
            );
        });

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
        abort_unless(Auth::check(), 403);
        $inst = session('lib_inst');
        $book = Book::where('inst', $inst)->where('id', $id)->firstOrFail();
        abort_unless(Auth::user()->canManageBook($book), 403);

        // Non-admin users may not delete books with physical book copies.
        if (!Auth::user()->isAdmin() && Book_copy::where('inst', $inst)
            ->where('bid', $id)->exists()) {
            return redirect('/book/'.$id.'/edit')->with('error', __('Book has physical copies. Ask an administrator.'));
        }

        DB::transaction(function () use ($book) {
            $book->delete();
        });

        // Filesystems are not transaction-aware; clean up only after DB commit.
        $msg = null;
        if ($book->cover_image) {
            Storage::delete('public/cover_images/'.$inst.'/'.$book->cover_image);
        }
        if (Storage::exists('ebook/'.$inst.'/'.$book->rid) &&
            !Storage::deleteDirectory('ebook/'.$inst.'/'.$book->rid)) {
            $msg = ' (Resource Directory Removal Error!)';
        }
        return redirect('/book')->with('success', __('Book Removed').$msg);
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
            
            $evalTxt="\$books=\\App\\Models\\Book::where(\$search_condition)";

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

    function man_files(Book $book) {
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
}