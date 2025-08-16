<?php
// This is not from Laravel but it is part of VWMLDBM
namespace wlibrary\book;
class Book { 
	public static $TB='book'; // table used for add,update,and del
	public static $STB='book'; // table used for search
	
	// public static $OPEN=10;
	// public static $REG_SUS=20;
	// public static $GRADE_CONFIRMED=30;
	// public static $CANCELED=90;
	
	public function __construct($id=null,$rid=null){
        $conn=$GLOBALS['conn'];
        $DTB_PRE=$GLOBALS['DTB_PRE'];		
		$this->TB__NAME=self::$TB;
        
        if($id) {
            $sql = "select * from $DTB_PRE"."_".self::$TB." where inst=".$_SESSION['lib_inst']." and id='$id'";	
        } else if($rid) {
            $sql = "select * from $DTB_PRE"."_".self::$TB." where inst=".$_SESSION['lib_inst']." and rid='$rid'";	
        } else {
			echo "Warning: Book id or rid is not set!<br>";
			return; // if no id or rid then return
		}

        $res = mysqli_query($conn,$sql);	
        if($res) $rs=mysqli_fetch_array($res);

		$this->inst=$rs['inst'];
		$this->id=$rs['id'];
				
		if(!$rs['rid']) {
			$rid=get_new_rid(); // random id for security. If rid is empty then get one
			$this->update('rid',$rid);
		}
		else $rid=$rs['rid'];
		$this->rid=$rid;
		
		$this->title=$rs['title'];
		$this->author=$rs['author'];
		$this->publisher=$rs['publisher'];
		$this->pub_date=$rs['pub_date'];
		$this->c_lang=$rs['c_lang'];						
		$this->isbn=$rs['isbn'];
		$this->eisbn=$rs['eisbn'];
		$this->reg_date=$rs['reg_date'];
		$this->price=$rs['price'];
		$this->cover_image=$rs['cover_image'];        
		$this->keywords=$rs['keywords'];
        $this->c_rtype=$rs['c_rtype'];
		$this->c_genre=$rs['c_genre'];
		$this->e_resource_yn=$rs['e_resource_yn'];
		$this->abstract=$rs['abstract'];
		$this->files=$rs['files'];
		$this->rfiles=$rs['rfiles'];
		$this->rdonly_pdf_yn=$rs['rdonly_pdf_yn'];
		$this->rdonly_video_yn=$rs['rdonly_video_yn'];
		$this->desc=$rs['desc'];
		$this->url=$rs['url'];
    }
    
    public function update($fd,$val){ // update field
		$conn=$GLOBALS['conn'];
        $DTB_PRE=$GLOBALS['DTB_PRE'];
        
		if($val=="" || $val==null) // the code should be null not ""
			$sql="update {$DTB_PRE}_{$this::$TB} set $fd=null where inst='{$_SESSION['lib_inst']}' and isNULL(id)=false AND id='{$this->id}'";
		else $sql="update {$DTB_PRE}_{$this::$TB} set $fd='$val' where inst='{$_SESSION['lib_inst']}' and isNULL(id)=false AND id='{$this->id}'";

        mysqli_query($conn,$sql);
		if(mysqli_affected_rows($conn)>0) {
			// $sql="update {$DTB_PRE}_{$this::$TB} set mtime='".date('Y-m-d H:i:s')."',mod_id='".$_SESSION['uid']."' where inst=".$_SESSION['lib_inst']." and isNULL(id)=false and id='$this->id'";
			// mysqli_query($conn,$sql);
			return true;
		}
		else return false;
    }
    
    public function file_exist($name){
		$fname_arr=explode(';',$this->files);
		if(array_search($name,$fname_arr)===false) return false;
		else return true;
    }
    
    public function get_rfile_name($fname){
		$fname_arr=explode(';',$this->files);
		$rfname_arr=explode(';',$this->rfiles);
		if(count($fname_arr)>0) 
			return $rfname_arr[array_search($fname,$fname_arr)];
    }
    
    public function get_file_name($rfname){
		$fname_arr=explode(';',$this->files);
		$rfname_arr=explode(';',$this->rfiles);
		if(count($fname_arr)>0) 
			return $fname_arr[array_search($rfname,$rfname_arr)];
    }
    
    public function get_new_files($del_file){
		$new_name=null;
		$fname_arr=explode(';',$this->files);
		foreach($fname_arr as $val) {
			if($val!=$del_file && $val!='') {				
				$new_name.=$val.";";
			}
		}
		return $new_name;
    }
    
	public function get_new_rfiles($del_rfile){
		$new_name=null;
		$frname_arr=explode(';',$this->rfiles);
		foreach($frname_arr as $val) 
			if($val!=$del_rfile && $val!='') {
				$new_name.=$val.";";
			}
		return $new_name;
	}


	public function favoredByUsers(){
		return $this->belongsToMany(User::class, 'book_user_favorites')->withTimestamps();
	}
}

function get_new_rid() { // random id for security (eg,for e-resource storage's folder name)
    $conn=$GLOBALS['conn'];
    $DTB_PRE=$GLOBALS['DTB_PRE'];	
    if(!isset($_SESSION['lib_inst'])) return; 
    while(true){
        $rid=rand(10000,1000000000); 
        $sql = "select count(rid) as cnt from {$DTB_PRE}_{Book::$TB} where inst='{$_SESSION['lib_inst']}' and rcid=$rcid";
		$res=mysqli_query($conn,$sql);
		if($res) $rs=mysqli_fetch_array($res);
        if($rs['cnt']>0) ;
        break;
    }
    return $rid;
}
