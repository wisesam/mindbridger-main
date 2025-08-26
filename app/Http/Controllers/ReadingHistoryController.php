<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
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
            $history->create_reading_toc($book_id);
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

    public function section_set_status(Request $request, $book_id)
    {
        $validated = $request->validate([
            'idx'  => 'required|integer',
            'status' => 'required|string|in:none,in_progress,completed',
        ]);

        $history = ReadingHistory::where('inst', session('lib_inst'))
            ->where('user_id', session('uid'))
            ->where('book_id', $book_id)
            ->first();

        $historyData = $history->historyData ?? [];
        if (is_string($historyData)) {
            $historyData = json_decode($historyData, true);
        }

        // Update one section by index
        if (isset($historyData[$validated['idx']])) {
            $historyData[$validated['idx']]['status'] = $validated['status'];
            if($validated['status'] == 'in_progress') {
                $historyData[$validated['idx']]['start_time'] = now()->toDateTimeString();
            } else if($validated['status'] == 'completed') {
                $historyData[$validated['idx']]['end_time'] = now()->toDateTimeString();
            } else if($validated['status'] == 'none') {
                $historyData[$validated['idx']]['start_time'] = null;
                $historyData[$validated['idx']]['end_time'] = null;
            }
            
        } else {
            return response()->json(['error' => 'Invalid index'], 400);
        }

        $history->historyData = $historyData;
        $history->save();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    public function section_ai_explain(Request $request, $book_id)
    {
        $start = intval($request->input('start_page'));
        $end   = intval($request->input('end_page'));

        // Find book text meta
        $inst = session('lib_inst') ?? 1;
        $meta = BookTextMeta::where('inst', $inst)
            ->where('book_id', $book_id)
            ->first();

        if (empty($meta)) {
            return response()->json(['error' => 'No BookTextMeta found'], 404);
        }

        $pages = json_decode($meta->text, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($pages)) {
            return response()->json(['error' => 'Invalid JSON in BookTextMeta.text'], 400);
        }

        // Collect text between start and end
        $sectionText = collect($pages)
            ->filter(fn($p) => $p['page'] >= $start && $p['page'] <= $end)
            ->pluck('text')
            ->implode("\n\n");

        if (!$sectionText) {
            return response()->json(['error' => 'No text found in given range'], 400);
        }

        $locale = app()->getLocale();
        $prompt = <<<EOT
        You are a helpful assistant for students.

        Given the following excerpt from a book:

        {$sectionText}

        1. Provide a clear explanation in {$locale} (7–10 sentences).  
        2. Generate 5 True/False questions (with correct answers).  
        3. Focus on the main story only (There could be before and after chuncked text). 
        4. Output **valid JSON** only, in this structure:

        {
        "explanation": "....",
        "questions": [
            {"q": "....?", "answer": true},
            {"q": "....?", "answer": false},
            ...
        ]
        }
        EOT;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => "You are a book section explainer. Always respond in {$locale}."],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.4,
        ]);

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? null;

        $cleanJson = preg_replace('/^```json|```$/i', '', trim($content));
        $data = json_decode(trim($cleanJson), true);

        return response()->json([
            'success'   => true,
            'book_id'   => $book_id,
            'start'     => $start,
            'end'       => $end,
            'meta_data' => $data
        ]);
    }
}
