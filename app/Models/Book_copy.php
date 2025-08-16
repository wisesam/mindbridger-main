<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // [SJH] Should be added because of composite primary key

class Book_copy extends Model
{
    // Table Name to be specified
    protected $table = 'book_copy';
    
    // Primary key
    public $primaryKey=['inst','id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst','id','bid', 'barcode', 'call_no','location','c_rstatus','comment'
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
        'barcode' => 'string',
        'call_no' => 'string',
    ];

    // Timestamps
    public $timestamps = false;

    public $incrementing = false;

    protected function setKeysForSaveQuery($query) // [SJH] Should be added because of composite primary key
    {
        return $query->where('inst', $_SESSION['lib_inst'])
                     ->where('id', $this->getAttribute('id'));
    }

    static protected function print_status_color($c,&$c_rstatus_available_arr) {
        if($c_rstatus_available_arr[$c]=='Y') return "green";
        else return "magenta";
    }
}
