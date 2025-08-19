<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Book; 

class ReadingHistory extends Model
{
    protected $table = 'reading_history';

    // define valid statuses as constants
    public const STATUS_NONE       = 'none';
    public const STATUS_INPROGRESS = 'in_progress';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_SUSPENDED  = 'suspended';

    public const VALID_STATUSES = [
        self::STATUS_NONE,
        self::STATUS_INPROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_SUSPENDED,
    ];
   

    protected $fillable = [
        'inst', 'book_id', 'user_id',
        'start_time', 'end_time',
        'status', 'historyData'
    ];

    protected $casts = [
        'historyData' => 'array',
        'evaluationData' => 'array',
        'start_time'  => 'datetime',
        'end_time'    => 'datetime',
    ];

    /**
     * Check if user has any history with given status
     */
    public function check_status($st)
    {
        if (!in_array($st, $this->VALID_STATUSES)) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        return (self->status == $st) ? true : false;
    }

    /**
    * Check if user has started reading
    */
   public function started()
   {
       return (!empty($this->status) && $this->status != self::STATUS_NONE) ? true : false;
   }

    /**
    * Check if the reading is finishable
    */
    public function finishable()
    {
        return ($this->status == self::STATUS_INPROGRESS || $this->status == self::STATUS_SUSPENDED) ? true : false;
    }
   
    /**
    * Check if the reading is finishable
    */
    public function startable()
    {
        return ($this->status == self::STATUS_NONE || empty($this->status)) ? true : false;
    }

     /**
     * creatae reading toc from book.auto_toc
     */
    public function create_reading_toc($book_id)
    {
        $book=Book::where('inst',session('lib_inst'))
        ->where('id',$book_id)->first();

        // Decode book's auto_toc
        $bookToc = json_decode($book->auto_toc, true) ?? [];
        
        // if there is no $book->auto_toc, it cannot proceeed the reading history
        if (empty($bookToc)) {
            return response()->json(['error' => 'No ToC found for this book'], 404);
        }

        // Extend each ToC entry
        $readingToc = collect($bookToc)->map(function ($item) {
            return [
                'title'      => $item['title'] ?? null,
                'page'       => $item['page'] ?? null,
                'start'      => $item['start'] ?? null,
                'end'        => $item['end'] ?? null,
                'level'      => $item['level'] ?? null,
        
                // per-ToC fields
                'start_time' => null,
                'end_time'   => null,
                'status'     => 'none',
        
                // timestamps inside JSON
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        })->toArray();
    
        // Save to reading_history
        $history = ReadingHistory::updateOrCreate(
            [
                'inst'    => session('lib_inst'),
                'user_id' => session('uid'),
                'book_id' => $book->id,
            ],
            [
                'historyData' => json_encode($readingToc),
            ]
        );
    
        return response()->json([
            'success' => true,
            'data'    => $history,
        ]);
    }
}
