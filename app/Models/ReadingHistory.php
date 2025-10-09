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
     * Check if user has any history with given status
     */
    public function status($st)
    {
        if($this->status == $st) {
            return true;
        } else {
            return false;
        }
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
        // Find book for current inst
        $book = Book::where('inst', session('lib_inst'))
            ->where('id', $book_id)
            ->first();

        if (!$book) {
            return null; // no book
        }

        // Decode book's auto_toc
        $bookToc = $book->auto_toc;
        if (is_string($bookToc)) {
            $bookToc = json_decode($bookToc, true) ?: [];
        } elseif (!is_array($bookToc)) {
            $bookToc = [];
        }
        if (empty($bookToc)) {
            return null; // no toc
        }

        // Get existing reading history (do NOT create here)
        $history = self::where('inst', session('lib_inst'))
            ->where('user_id', session('uid'))
            ->where('book_id', $book->id)
            ->first();

        if (!$history) {
            return null; // caller must have created row beforehand
        }

        // 🔒 Do NOT fill if historyData already exists in DB (even if it's "[]")
        // Use raw value to distinguish NULL vs existing JSON
        $raw = method_exists($history, 'getRawOriginal')
            ? $history->getRawOriginal('historyData')
            : $history->historyData; // fallback (still fine in most cases)

        if (!is_null($raw)) {
            // historyData column already has a value (could be "[]"), so just return
            return $history;
        }

        // Build initial reading ToC payload (only when column was NULL)
        $readingToc = collect($bookToc)->map(function ($item, $index) {
            return [
                'idx'        => $index,
                'title'      => $item['title'] ?? null,
                'page'       => $item['page'] ?? null,
                'start'      => $item['start'] ?? null,
                'end'        => $item['end'] ?? null,
                'level'      => $item['level'] ?? null,

                // per-section state (blank)
                'start_time' => null,
                'end_time'   => null,
                'status'     => self::STATUS_NONE,
                'summary'    => null,
            ];
        })->values()->toArray();

        $history->historyData = $readingToc;
        $history->save();

        return $history;
    }

}
