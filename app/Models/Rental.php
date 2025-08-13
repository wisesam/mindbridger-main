<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // [SJH] Should be added because of composite primary key
use Illuminate\Support\Facades\DB;

class Rental extends Model
{
    // Table Name to be specified
    protected $table = 'rental';
    
    // Primary key
    public $primaryKey=['inst','id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst','id','bcid', 'uid', 'rent_date','due_date','return_date','c_rent_status','rcomment'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'bcid' => 'integer',
        'uid' => 'string',
        'rent_date' => 'date:Y-m-d H:i:s',
        'due_date' => 'date:Y-m-d H:i:s',
        'return_date' => 'date:Y-m-d H:i:s',
    ];

    // Timestamps
    public $timestamps = false;

    public $incrementing = false;

    protected function setKeysForSaveQuery($query) // [SJH] Should be added because of composite primary key
    {
        return $query->where('inst', $this->getAttribute('inst'))
                     ->where('id', $this->getAttribute('id'));
    }

    public function isOverdue($rental_terminated_yn_arr){
        // Check if this rental is terminated (eg, returned)
        if($rental_terminated_yn_arr[$this->c_rent_status]=='Y'){ // rental is terminated. eg, returned, lost            
            if($this->return_date > $this->due_date) return 1; // terminated but late
            else return 0; // okay    
        }
        else { // Rental is still ongoing
            if(date('Y-m-d H:i:s') > $this->due_date) return 2; // should be returned
            else return 0; // okay  
        }
    }
    
    public static function is_new_rental_ok($bcid){
        $rental=Rental::where('inst', $_SESSION['lib_inst'])
            ->where('bcid', $bcid)->where('c_rent_status','<>','20')->first();
        if(isset($rental->id)) return false;
        else return true;
    }

    static protected function num($opt=null,$uid=null){
        if($opt=='RENTED') {
            $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code',null,null," and rented_yn='Y'");
            if($uid) $cnt=Rental::where('inst',$_SESSION['lib_inst'])->where('c_rent_status',$rented_code)->where('uid',$uid)->count();
            else $cnt=Rental::where('inst',$_SESSION['lib_inst'])->where('c_rent_status',$rented_code)->count();
        }          
        else  {
            if($uid) 
                $cnt=Rental::where('inst',$_SESSION['lib_inst'])->where('uid',$uid)->count(); 
            else $cnt=Rental::where('inst',$_SESSION['lib_inst'])->count(); 
        } 
        
        return $cnt;
    }

    static protected function num_by_book_copy($opt=null,$bcid=null){
        if($opt=='RENTED') {
            $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code',null,null," and rented_yn='Y'");
            if($bcid) $cnt=Rental::where('inst',$_SESSION['lib_inst'])->where('c_rent_status',$rented_code)->where('bcid',$bcid)->count();            
            else $cnt=Rental::where('inst',$_SESSION['lib_inst'])->where('c_rent_status',$rented_code)->count();
        }
        else  {
            if($bcid)
                $cnt=Rental::where('inst',$_SESSION['lib_inst'])->where('bcid',$bcid)->count(); 
            else $cnt=Rental::where('inst',$_SESSION['lib_inst'])->count(); 
        } 

        return $cnt;
    }

    static protected function late_rental_check($uid=null,$id=null,$bcid=null,$opt=null) {
        $search_condition=array();
        $search_condition []=['inst',$_SESSION['lib_inst']];
        if($uid) $search_condition[]=['uid',$uid];
        if($id) $search_condition[]=['id',$id];
        if($bcid) $search_condition[]=['bcid',$bcid];

        $cnt=Rental::where($search_condition)->where('due_date','<=',date('Y-m-d H:i:s'))->count();       

        if($cnt) return true;
        else return false;
    }

    static protected function print_status_color($c,&$rental_terminated_yn_arr) {
        if($rental_terminated_yn_arr[$c]=='Y') return "green";
        else return "magenta";
    }
}