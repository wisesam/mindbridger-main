<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\book;

class BookUserEshelf extends Model
{
    use HasFactory;

    protected $table = 'book_user_eshelf';

    protected $fillable = [
        'inst',
        'user_id',
        'book_id',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
                    ->where('inst', $this->inst);
    }

    /**
     * Relationship to Book
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id')
                    ->where('inst', $this->inst);
    }
}
