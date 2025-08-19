<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Book; 
use App\Models\BookTextMeta; 
use App\Models\BookUserEshelf;
use App\Models\ReadingHistory;

require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");
require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root')."/app/Libraries/book.php");

class ReadingHistoryController extends Controller
{
    /**
     * Reading history for current user
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $inst   = $request->user()->inst;

        $histories = ReadingHistory::where('inst', $inst)
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        $result = [];

        foreach ($histories as $history) {
            $book = Book::where('inst', $inst)
                ->where('id', $history->book_id)
                ->first();

            if (!$book) {
                continue;
            }

            // decode book's ToC
            $toc = [];
            if (!empty($book->auto_toc)) {
                $toc = is_array($book->auto_toc) ? $book->auto_toc : json_decode($book->auto_toc, true);
            }

            // decode historyData
            $historyData = $history->historyData ?? [];

            $sections = [];
            foreach ($toc as $idx => $section) {
                $sectionHistory = $historyData[$idx] ?? null;

                $sections[] = [
                    'idx'        => $idx,
                    'title'      => $section['title'] ?? 'Untitled',
                    'start'      => $section['start'] ?? null,
                    'end'        => $section['end'] ?? null,
                    'level'      => $section['level'] ?? 1,
                    'start_time' => $sectionHistory['start_time'] ?? null,
                    'end_time'   => $sectionHistory['end_time'] ?? null,
                    'status'     => $sectionHistory['status'] ?? ReadingHistory::STATUS_NONE,
                ];
            }

            $result[] = [
                'book_id'        => $history->book_id,
                'book_title'     => $book->title ?? 'Untitled',
                'overall_status' => $history->status,
                'sections'       => $sections,
            ];
        }

        return response()->json($result);
    }

    /**
     * Check if a history exists for this book
     */
    // public function check(Request $request, $book_id)
    // {
    //     $exists = ReadingHistory::where('inst', $request->user()->inst)
    //         ->where('user_id', $request->user()->id)
    //         ->where('book_id', $book_id)
    //         ->exists();

    //     return response()->json(['exists' => $exists]);
    // }

    /**
     * Start or update a reading session
     */
    public function store(Request $request, $book_id)
    {
        $now = Carbon::now();

        $history = ReadingHistory::where('inst', $request->user()->inst)
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book_id)
            ->first();

        if ($history) {
            $history->update([
                'status'     => ReadingHistory::STATUS_INPROGRESS,
                'start_time' => $history->start_time ?: $now,
            ]);
        } else {
            ReadingHistory::create([
                'inst'       => $request->user()->inst,
                'user_id'    => $request->user()->id,
                'book_id'    => $book_id,
                'start_time' => $now,
                'status'     => ReadingHistory::STATUS_INPROGRESS,
                'historyData'  => [],
                'evaluationData'  => [],
            ]);
        }

        return response()->json(['message' => 'Reading session started']);
    }

    
    /**
     * Update the status of a reading history
     */
    public function set_status(Request $request, $book_id)
    {
        $validated = $request->validate([
            'book_id' => 'required|integer',
            'status'  => 'required|string'
        ]);

        // Example: save or update reading history
        $history = ReadingHistory::updateOrCreate(
            [
                'inst'    => session('lib_inst'),
                'user_id' => session('uid'),
                'book_id' => $validated['book_id'],
            ],
            ['status' => $validated['status']]
        );

        if($request->operation == 'create_history') {
            $history->historyData = $history->create_reading_toc($book_id);
            $history->save();
        }

        return response()->json(['success' => true, 'data' => $history]);
    }

    /**
     * Delete reading history for a book
     */
    public function destroy(Request $request, $book_id)
    {
        $deleted = ReadingHistory::where('inst', session('lib_inst'))
            ->where('user_id', session('uid'))
            ->where('book_id', $book_id)
            ->delete();
        return response()->json(['success' => $deleted, 'data' => null]);
    }
}
