<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // [SJH] Should be added because of composite primary key
use Illuminate\Support\Facades\DB;
use App\Book_copy; // use the model that we defined [SJH]

class Book extends Model
{
    // Table Name to be specified
    protected $table = 'book';
    
    // Primary key
    public $primaryKey=['inst','id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst','id','title', 'author', 'c_rtype','c_genre','publisher','pub_date','isbn','eisbn','c_lang','keywords','e_book_yn','cover_image','desc','url','price','c_grade','c_category','c_category2','hide_yn','hide_from_guest_yn','toc','rfiles'
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
        'title' => 'string',
        'author' => 'string',
        'pub_date' => 'date:Y-m-d H:i:s',
        'reg_date' => 'date:Y-m-d H:i:s',
        'auto_toc' => 'array',  // lets you read/write it as PHP array
    ];


    // Timestamps
    public $timestamps = false;

    public $incrementing = false;

    protected function setKeysForSaveQuery($query) // [SJH] Should be added because of composite primary key
    {
        return $query->where('inst', $this->getAttribute('inst'))
                     ->where('id', $this->getAttribute('id'));
    }

    public function get_copy_num() {
        $res = DB::select("select count(id) as cnt from ".config('database.connections.mysql.prefix')."book_copy where inst='{$_SESSION['lib_inst']}' and bid='{$this->getAttribute('id')}'");
        return $res[0]->cnt;
	}
}