<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [SJH]
use Illuminate\Support\Facades\DB; // [SJH]
use Illuminate\Support\Facades\Hash; // [SJH]

use App\Models\User; // [SJH]
use App\Models\Book; // [SJH]
use App\Models\Announcement; // [SJH]
use App\Models\BookUserFavorite;  // [SJH] 2025.05.21

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class RootController extends Controller {
    public function index() {
        // [SJH] If there is no Super Admin user (just installed), add one
        if(! Auth::check()) {
            if(User::count_super_admin() < 1) {
                if(!config('app.multi_inst','')) {
                    $theInst= new \vwmldbm\code\Inst_var(null,config('app.inst_uname','')); // [TBM] Where $inst_uname?
                    $inst=$theInst->no ?? 1;
                    session(['lib_inst' => $inst]);
                }
                else $inst=session('lib_inst');                              
                if(!$inst) {
                    return redirect('/');                
                }
                if($inst==config('app.inst',1)) { // Super Institution: during system set up
                    $email=(config('app.wv2_sadmin_email')?config('app.wv2_sadmin_email'):'libadmin'.\wlibrary\code\genRandStr(4).'@wise4edu.com');
                }
                else { // Normal institution(Not Super): during system set up
                    $email='libadmin'.\wlibrary\code\genRandStr(4).'@wise4edu.com';
                }
                
                $email_pwd=\wlibrary\code\genRandStr(10);
                $admUser=User::create([
                    'inst' =>$inst,
                    'id' => 'libadmin',
                    'name' => 'Library Super Admin',
                    'email' => $email,
                    'utype' => 'SA',
                    'ustatus' => 'A',
                    'password' => Hash::make($email_pwd),
                ]);
                
                if(isset($admUser)) {
                    echo "<h2 style='color:red' class='container'>Super Admin was created!</h2>";
                    echo "<div style='color:red' class='container'>
                        ID : {$admUser->id} <br>
                        Email : {$admUser->email} <br>
                        Password : $email_pwd <br>
                    </div>";
                }
                else {
                    echo "<h2 class='container' style='color:red;'>Super Admin couldn't be created!</h2>";
                }
            } // End of check Super Admin user and add one
        } 
        
        if(Auth::check()) {
            if(!session()->has('lib_inst')){ // session expired by WISE or other activity
                Auth::logout();
                if(config('app.multi_inst')) return redirect('/inst');
                else  return redirect('/');
            }
            else {
                $this::register_extra_session(); // register extra session variables for ebook access by url
               // This may not comply with Laravel's philosophy but can't find other solution. Set from BookController.php
                if((session()->has('specialRedirect'))) {                     
                    $book=Book::where('inst',session('lib_inst'))
                    ->where('id',session('specialBookId'))->first();
                    
                    session()->forget('specialRedirect');
                    session()->forget('specialBookId');

                    if(isset($book->id))  return redirect('/book/'.$book->id);
                    else return redirect('/book/')->with('warning',__("The resource does not exist!"));
                }
                
                // Show books on Carousel
                //$books_raw=Book::where('inst',$_SESSION['lib_inst'])->whereNotNull('cover_image')->orderBy('id','desc')->take(100)->get();      
                $books_raw=Book::where('inst',session('lib_inst'))->orderBy('id','desc')->take(100)->get();      
                
                // Randomize the order
                $books=array();
                foreach($books_raw as $b) {
                    $books[rand()*100000]=$b;
                }                        
                ksort($books); // sort

                if(Auth::user()->isAdmin()) {  // Admin user                
                    session(['wlibrary_admin' => 'A']); // for VWMLDBM Admin (mindbridger)
                    return view('admin.AdminDashboard')->with('frontHome',true)->with('books',$books);
                }
                else { // normal user logged in
                    $fav = BookUserFavorite::where('user_id', session('uid'))
                    ->where('inst', Auth::user()->inst)
                    ->get();
                    $numFavorites=$fav->count();
                    return view('root.home')->with('frontHome',true)->with('books',$books)->with('numFavorites',$numFavorites);
                }
            }
        }
        else if(!session()->has('lib_inst')){ // if session expired, redirect to /
            return redirect('/login');
        }
        else {
            $this::register_extra_session();

            // Show books on Carousel
            $books_raw=Book::where('inst',session('lib_inst'))->whereNotNull('cover_image')->orderBy('id','desc')->take(100)->get();      
            
            // Randomize the order
            $books=array();
            foreach($books_raw as $b) {
                $books[rand()*100000]=$b;
            }                        
            ksort($books); // sort

            return view('root.index')->with('frontHome',true)->with('books',$books);
        }
    }

    public function wv2login(Request $request) {
        $data=(Array) wv2_decode($request->get('msg'),$request->get('msg2'),$request->get('inst_uname'));
			
		if(!isset($data['wv2uid'])){
            return redirect('/')->with('error',__("Auth failed!"));
        }

        $u = User::where('inst',session('lib_inst'))
                ->where('id',$data['wv2uid'])->first();

        if(isset($u->id)) { // User exists            
            if($data['wv2utype']=='A') {
                $wv2LibAdmin=$u->is_wv2_lib_Admin($data['c_adm_role']);
                if($wv2LibAdmin==false) { // only WV2's sysadmin, library admin can login as admin                    
                    return redirect('/')->with('error',__("Your account is not allowed!"));
                }
            }
      
            if($u->code_c_utype && $data['wv2utype']!=$u->get_wv2utype()) { // type mismatch, so 1st update it 
                $u->code_c_utype=$u->get_w2utype_code($data['wv2utype']);
                $u->ustatus=$data['wv2utype'];                
       
                User::where('inst', $u->inst)
                ->where('id', $u->id)
                ->update( ['code_c_utype' => $u->code_c_utype,
                    'ustatus' => $u->ustatus]);
                
                // die($u->code_c_utype.$u->ustatus);
                // // $u->update(), $u->save() is not working! Update SQL doesn't have inst='$inst' !? 
                // $u->update(               
                //     ['code_c_utype' => $u->code_c_utype,
                //     'ustatus' => $u->ustatus]);
                Auth::login($u); 
                return redirect('/');
            }
            else { // type matched, so everything is okay
                $u->ustatus=$data['wv2utype'];
                $u->update(['ustatus' => $u->ustatus]); 
                Auth::login($u);
                return redirect('/');
            }
        }
        else {  // User doesn't exist, so register first
            $theInst= new \vwmldbm\code\Inst_var(null,$data['wv2inst_id']);

            if($data['wv2utype']=='A') {
                $wv2LibAdmin=User::is_wv2_lib_Admin($data['c_adm_role']);
                if($wv2LibAdmin==false) { // only WV2's sysadmin, library admin can login as admin
                    return redirect('/')->with('error',__("Your account is not allowed!"));
                }
                else if($wv2LibAdmin=='SA' || $wv2LibAdmin=='A') { // Library Super Admin
                    if(!$data['wv2email']) { // email doesn't exist cannot register
                        return redirect('/')->with('error',__("User email doesn't exist!"));
                    }
                    
                    if(User::is_dup_email($data['wv2email'])) {
                        return redirect('/')->with('error',__("User email already exists!"));
                    }

                    $newU=array();
                    $newU['inst']=$theInst->no;
                    $newU['id']=$data['wv2uid'];
                    $newU['name']=$data['wv2uname'];
                    $newU['email']=$data['wv2email'];
                    $newU['utype']=$wv2LibAdmin;
                    $newU['code_c_utype']=User::get_w2utype_code('A'); 
                    $newU['ustatus']='A';
                    $u=User::create($newU);

                    Auth::login($u);
                    return redirect('/')->with('success',__("User registered!"));;
                }
            }
            else { // Member User (Not Admin)
                if(!$data['wv2uid']) { // Illegal Authentification                    
                    die("Illegal Authentification!");
                }

                if(!isset($data['wv2email'])) { // email doesn't exist cannot register
                    return redirect('/')->with('error',__("User email doesn't exist!"));
                }

                if(User::is_dup_email($data['wv2email'])) {
                    return redirect('/')->with('error',__("User email already exists!"));
                }

                $newU=array();

                $newU['inst']=$theInst->no;
                $newU['id']=$data['wv2uid'];
                $newU['name']=$data['wv2uname'];
                $newU['email']=$data['wv2email'];
                $newU['utype']='M';
                $newU['code_c_utype']=USER::get_w2utype_code($data['wv2utype']); 
                $newU['ustatus']='A';

                if(!isset($newU['code_c_utype'])){ // User Type from WISE(School ERP), doesn't support here in Lib sys
                    return redirect('/')->with('error',__("This user type is not supported!"));
                }

                $u=User::create($newU);

                Auth::login($u);
                return redirect('/')->with('success',"User registered!");
            }
        }
    }

    public function inst() {
        if(!config('app.multi_inst')) return  redirect('/')->with('error',__("Multi-Institution is not available!"));
        return view('auth.inst');
    }

    public function inst_uname($inst_uname) {
        $theInst= new \vwmldbm\code\Inst_var(null,$inst_uname);
        if(!isset($theInst->no)) { // not exist
            return redirect('/inst')->with('error',__("Institution, \"".$inst_uname." \" doesn't exist."));
        }

        if(session()->has('lib_inst') && session('lib_inst')!=$theInst->no) { // Trying inst is different from already in inst
            session_unset();
            session_destroy();
            Auth::logout();
            session_start();
            session(['lib_inst' => $theInst->no]);
            session(['inst_uname' => $theInst->inst_uname]);
            $this::register_extra_session();
            return redirect('/');
        }
        else { 
            session(['lib_inst' => $theInst->no]);
            session(['inst_uname' => $theInst->inst_uname]);
            $this::register_extra_session();
            return redirect('/');
        }
    }

    public function inst_uname_book($inst_uname,$book_id) {
        $theInst= new \vwmldbm\code\Inst_var(null,$inst_uname);
        if(!isset($theInst->no)) { // not exist
            return redirect('/inst')->with('error',__("Institution, \"".$inst_uname." \" doesn't exist."));
        }

        if(session()->has('lib_inst') && session('lib_inst')!=$theInst->no) { // Trying inst is different from already in inst
            session_unset();
            session_destroy();
            Auth::logout();
            session_start();
            session(['lib_inst' => $theInst->no]);
            session(['inst_uname' => $theInst->inst_uname]);
            $this::register_extra_session();
            return redirect('/book/'.$book_id);
        }
        else { 
            session(['lib_inst' => $theInst->no]);
            session(['inst_uname' => $theInst->inst_uname]);
            $this::register_extra_session();
            return redirect('/book/'.$book_id);
        }

        
    }

    public function inst_process(Request $request) {
        $theInst= new \vwmldbm\code\Inst_var(null,trim($request->input('institution')));
        if(!isset($theInst->no)) { // not exist
            return redirect('/inst')->with('error',__("Institution, \"".$request->input('institution')." \" doesn't exist."));
        }
        session(['lib_inst' => $theInst->no]);
        session(['inst_uname' => $theInst->inst_uname]);
        $this::register_extra_session();
        return redirect('/');
    }

    static private function register_extra_session() { 
        session(['app.root' => config('app.root','')]); // to pass it to progress_up.php for e-resource upload
        session(['app.root2' => config('app.root2','')]); // to pass it to progress_up.php for e-resource upload
        session(['app.url' => config('app.url','')]); // to pass it to progress_up.php for e-resource upload

        $_SESSION['lib_inst'] = session('lib_inst'); // to pass it to progress_up.php for e-resource upload
        $_SESSION['inst_uname'] = session('inst_uname'); // to pass it to progress_up.php for e-resource upload
        $_SESSION['app.root'] = config('app.root',''); // to pass it to progress_up.php for e-resource upload
        $_SESSION['app.root2'] = config('app.root2',''); // to pass it to progress_up.php for e-resource upload
        $_SESSION['app.url'] = config('app.url',''); // to pass it to progress_up.php for e-resource upload
    }
}

function wv2_decode($ciphertext,$iv_hex,$inst_uname=null) {
    $cipher = "aes-256-cbc";
    if(!session()->has('lib_inst')){ // login request from WISE/Acad system (not from wlibrary)
        if(!$inst_uname) redirect('/')->with('error',"Illegal Access"); // illegal access
        $theInst=new \vwmldbm\code\Inst_var(null,$inst_uname);
        session(['lib_inst' => $theInst->no]);
    }

    if(!isset($theInst)) $theInst=new \vwmldbm\code\Inst_var(session('lib_inst'));
    if(session('lib_inst')==config('app.inst')) { // super inst
        $key=config('app.inst_secret').config('app.host');
    }
    else {
		if(session('lib_inst')=='2')  print_r($theInst);
        $key=trim($theInst->secret).trim($theInst->host);
    }
    $iv=hex2bin($iv_hex);

    if($key && in_array($cipher, openssl_get_cipher_methods())) {
       // echo $ciphertext."111".$cipher."222".$key."333".$iv."444";
        $original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv);
        $data=json_decode($original_plaintext);
        return $data;
    }
}