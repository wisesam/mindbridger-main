<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Needed for composite primary key
use Illuminate\Support\Facades\DB;
use App\Book_copy; // Use the model that we defined

class Book extends Model
{
    // Table name
    protected $table = 'book';

    // Composite primary key
    public $primaryKey = ['inst', 'id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inst',
        'id',
        'title',
        'author',
        'c_rtype',
        'c_genre',
        'publisher',
        'pub_date',
        'isbn',
        'eisbn',
        'c_lang',
        'keywords',
        'e_book_yn',
        'cover_image',
        'desc',
        'url',
        'price',
        'c_grade',
        'c_category',
        'c_category2',
        'hide_yn',
        'hide_from_guest_yn',
        'toc',
        'rfiles',
        'meta_data',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // meta_info is used for storing additional metadata in JSON format
    // which is extracted by AI API
    protected $hidden = [
        'auto_toc',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'title'    => 'string',
        'author'   => 'string',
        'pub_date' => 'date:Y-m-d H:i:s',
        'reg_date' => 'date:Y-m-d H:i:s',
        // 'auto_toc' => 'array',  // lets you read/write it as PHP array
    ];

    // Disable default timestamps
    public $timestamps = false;

    // Disable auto-incrementing since we use composite primary key
    public $incrementing = false;

    /**
     * Override save query for composite primary key
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('inst', $this->getAttribute('inst'))
                     ->where('id', $this->getAttribute('id'));
    }

    /**
     * Get the number of copies of this book.
     *
     * @return int
     */
    public function get_copy_num()
    {
        $res = DB::select(
            "SELECT COUNT(id) AS cnt 
             FROM " . config('database.connections.mysql.prefix') . "book_copy 
             WHERE inst = '{$_SESSION['lib_inst']}' 
             AND bid = '{$this->getAttribute('id')}'"
        );

        return $res[0]->cnt;
    }


    // accessor for the show URL
    public function getShowUrlAttribute()
    {
        return route('book.show', ['book' => $this->id]);
    }

    // Scope for per-user visibility + current inst
    public function scopeVisibleTo(Builder $q, $user = null): Builder
    {
        $user = $user ?? Auth::user();

        $q->where('inst', session('lib_inst'));

        if (!$user) {
            // Guest: hide books with hide_from_guest_yn=Y OR hide_yn=Y
            return $q->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('hide_from_guest_yn', '<>', 'Y')
                        ->orWhereNull('hide_from_guest_yn');
                })->where(function ($sub) {
                    $sub->where('hide_yn', '<>', 'Y')
                        ->orWhereNull('hide_yn');
                });
            });
        }

        if (method_exists($user, 'isAdmin') && !$user->isAdmin()) {
            return $q->where(function ($sub) {
                $sub->where('hide_yn', '<>', 'Y')
                    ->orWhereNull('hide_yn');
            });
        }

        return $q; // Admin sees all
    }
}