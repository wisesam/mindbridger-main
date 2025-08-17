<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookTextMeta extends Model
{
    // Table name
    protected $table = 'book_text_meta';

    // Primary key (default is "id", so this line is optional)
    protected $primaryKey = 'id';

    // Allow mass assignment
    protected $fillable = [
        'inst',
        'book_id',
        'meta',
        'text',
    ];

    // Laravel manages created_at and updated_at
    public $timestamps = true;

    // Casts
    protected $casts = [
        'meta' => 'array',   // stored as JSON, used as PHP array
        'text' => 'string',  // plain string (could also stay uncast)
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id')
                    ->where('inst', $this->inst);
    }
}
