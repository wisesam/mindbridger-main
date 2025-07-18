<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // [SJH] Should be added because of composite primary key
use Illuminate\Support\Facades\DB;

class Announcement extends Model
{
    // Table Name to be specified
    protected $table = 'announcement';
    
    // Primary key
    public $primaryKey=['inst','id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst','id', 'title', 'body','top_yn','create_id','mod_id','ctime','mtime'
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
        'ctime' => 'date:Y-m-d',
        'mtime' => 'date:Y-m-d',
    ];

    // Timestamps
    public $timestamps = false;

    public $incrementing = false;

    protected function setKeysForSaveQuery($query) // [SJH] Should be added because of composite primary key
    {
        return $query->where('inst', $_SESSION['lib_inst'])
            ->where('id', $this->getAttribute('id'));
    }

}