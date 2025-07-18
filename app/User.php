<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Libraries\Code; // [SJH]

class User extends Authenticatable
{
    use Notifiable;
    
    
    // // Primary key
    // public $primaryKey=['inst','id'];
    public $primaryKey='email';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst', 'id', 'name', 'email', 'password','code_c_utype','utype','ustatus',
    ];

    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $incrementing = false; // SJH to treat ID as string not int
    
    // protected function setKeysForSaveQuery(Builder $query) // [SJH] Should be added because of composite primary key
    // {
    //     return $query->where('inst', session('lib_inst'))
    //                  ->where('id', $this->getAttribute('id'));
    // }

    /** 
     * [SJH] Count the number of users
     *
     * @var integer
     */
    static protected function num($opt=null){      
        if($opt=='EXCEPT_ADMIN') 
            $cnt=User::where('inst',session('lib_inst'))->where('utype','<>','SA')->where('utype','<>','A')->count();
        else $cnt=User::where('inst',session('lib_inst'))->count();

        return $cnt;
    }

    /** 
     * [SJH] Count the number of Super Admin
     *
     * @var integer
     */
    static protected function count_super_admin(){
        try {
            $res = DB::select("select count(id) as cnt from ".config('database.connections.mysql.prefix')."users where inst='".session('lib_inst')."' and utype='SA'");
                    
        // $res = User::select('id')
        //     ->where('utype','10')->take(1)->get();

            return $res[0]->cnt;

        } catch (\Exception $e) {
           if($e->getCode() == '42S02') die("Please Contact the webmaster"); // [SJH] default inst is 1
        }
    }

     /** 
     * [SJH] return whether the user is admin or not
     *
     * @var bool
     */
    public function isAdmin($which=null,$uid=null) { // [SJH]

        if($uid) { // user is given
            $u=new \App\User(['inst'=>session('lib_inst'),'id'=>$uid]);
            if(!$which && ($u->utype=='SA'||$u->utype=='A')) return true;
            else if($which=='SA' && $u->utype=='SA') return true;
            else if($which=='A' && $u->utype=='A') return true;
            else return false;
        }
        else {  // user is not given
            if(!$which && ($this->utype=='SA'||$this->utype=='A')) return true;
            else if($which=='SA' && $this->utype=='SA') return true;
            else if($which=='A' && $this->utype=='A') return true;
            else return false;
        }
    }

    public static function checkAdmin($which,$uid) { // [SJH]
        if($uid) { // user is given
            $arr=Array('inst','id');
            $u=new \App\User($arr);
            if(!$which && ($u->utype=='SA'||$u->utype=='A')) return true;
            else if($which=='SA' && $u->utype=='SA') return true;
            else if($which=='A' && $u->utype=='A') return true;
            else return false;
        }
    }

    // protected $appends = [ 'custom' ];  // added by [SJH]. Is it working? 
    
    public static function print_utype($field_name=null, $t=null,$fevent=null,$opt=null) {
        if(!$field_name) $field_name="utype";
        $super_admin_selected=null;
        $admin_selected=null;
        $guest_selected=null;
        $member_selected=null;
        $default_selected=null;

        if($t=='SA') $super_admin_selected=" selected";
        else if($t=='A') $admin_selected=" selected";
        else if($t=='M') $member_selected=" selected";
        else if($t=='G') $guest_selected=" selected";
        else $default_selected=" selected";

        if($opt=='SHORT') {
            $rval="<select name='$field_name' class='form-control w-auto d-inline ' $fevent>
                <option value='' $default_selected>-- ".Lang::get('messages.select')." --</option>";
        }
        else {
            $rval="<select name='$field_name' class='browser-default custom-select' $fevent>
                <option value='' $default_selected>-- ".Lang::get('messages.select')." --</option>";
        }
        
        
        if(Auth::user()->isAdmin('SA')) $rval.=" <option value='SA' $super_admin_selected>".__('messages.sup_admin')."</option>";
       
        $rval.="<option value='A' $admin_selected>".__('messages.admin')."</option>
            <option value='M' $member_selected>".__('messages.member')."</option>
            <option value='G' $guest_selected>".__('messages.guest')."</option> 
        </select>";

        return $rval;
    }

    public static function get_utype($t) {
        if($t=='SA') return(__('messages.sup_admin'));
        else if($t=='A') return(__('messages.admin'));
        else if($t=='M') return(__('messages.member'));
        else if($t=='G') return(__('messages.guest'));
    }

    public function get_wv2utype() {
        $inst=session('lib_inst'); 
        $res = DB::select("select w2_utype from ".config('database.connections.mysql.prefix')."code_c_utype where inst='$inst' and c_lang='10' and code='{$this->code_c_utype}'");
        if(isset($res[0]->w2_utype))
			return $res[0]->w2_utype;
    }

    public static function get_w2utype_code($c) {
        $inst=session('lib_inst'); 
        $res = DB::select("select code from ".config('database.connections.mysql.prefix')."code_c_utype where inst='$inst' and c_lang='10' and w2_utype='$c' order by default_utype_yn desc");
        if(isset($res[0]->code))
			return $res[0]->code;
    }

    public static function is_wv2_lib_Admin($c) {
        if(session('lib_inst')==1) { // super institution, so read from config
            $sa=config('app.wv2_lib_super_admin');
            $a=config('app.wv2_lib_admin');
        }
        else {
            $theInst=new \vwmldbm\code\Inst_var(session('lib_inst'));
            $sa=explode(',',$theInst->other_prg_sadm);
            $a=explode(',',$theInst->other_prg_adm);
        }

        if(array_search($c,$sa)!==false) return "SA";
        else if(array_search($c,$a)!==false) return "A";
        else false;
    }

    public static function print_ustatus($field_name=null, $t=null,$fevent=null) {
        if(!$field_name) $field_name="ustatus";
        $active_selected=null;
        $inactive_selected=null;
        $suspended_selected=null;
        $pwd_suspended_selected=null;
        $terminated_selected=null;
        $default_selected=null;
        
        if($t=='A') $active_selected=" selected";
        else if($t=='I') $inactive_selected=" selected";
        else if($t=='S') $suspended_selected=" selected";
        else if($t=='PS') $pwd_suspended_selected=" selected";
        else if($t=='T') $terminated_selected=" selected";
        else $default_selected=" selected";

        $rval="<select name='$field_name' class='browser-default custom-select' $fevent>
                <option value='' $default_selected>-- ".Lang::get('messages.select')." --</option>";
        
        $rval.="<option value='A' $active_selected>".__('messages.active')."</option>
            <option value='I' $inactive_selected>".__('messages.inactive')."</option>
            <option value='S' $suspended_selected>".__('messages.suspended')."</option> 
            <option value='PS' $pwd_suspended_selected>".__('messages.pwd_suspended')."</option> 
            <option value='T' $terminated_selected>".__('messages.terminated')."</option> 
        </select>";

        return $rval;
    }

    public static function get_ustatus($t) {
        if($t=='A') return(__('messages.active'));
        else if($t=='I') return(__('messages.inactive'));
        else if($t=='S') return(__('messages.suspended'));
        else if($t=='PS') return(__('messages.pwd_suspended'));
        else if($t=='T') return(__('messages.terminated'));
    }

    public static function is_dup_email($email) {
        $inst=session('lib_inst'); 
        $res = DB::select("select id from ".config('database.connections.mysql.prefix')."users where email='$email'");
        if(isset($res[0]->id)) return $res[0]->id;
		else return null;
    }
}