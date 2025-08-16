<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // [SJH] Should be added because of composite primary key
use Illuminate\Support\Facades\DB;

class About extends Model
{
    // Table Name to be specified
    protected $table = 'about';
    
    // Primary key
    public $primaryKey=['inst'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst','header', 'footer', 'about_txt','open_hours','country','state','city','address','zip','phone','fax','email','contact_info'
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
        'email' => 'email',
    ];

    // Timestamps
    public $timestamps = false;

    public $incrementing = false;

    // protected function setKeysForSaveQuery(Builder $query) // [SJH] Should be added because of composite primary key   6.2 version
    protected function setKeysForSaveQuery($query) // [SJH] Should be added because of composite primary key
    {
        return $query->where('inst', $this->getAttribute('inst'));
    }
}