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
require_once(config('app.root')."/app/Helpers/bookViewHelpers.php");

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

            if (!$book) continue;

            // decode book's ToC
            $toc = [];
            if (!empty($book->auto_toc)) {
                $toc = is_array($book->auto_toc) ? $book->auto_toc : (json_decode($book->auto_toc, true) ?: []);
            }

            // decode historyData
            $historyData = $history->historyData ?? [];
            if (is_string($historyData)) $historyData = json_decode($historyData, true) ?: [];

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
                    'summary'    => $sectionHistory['summary'] ?? null, // <-- expose summary if exists
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
                'inst'          => $request->user()->inst,
                'user_id'       => $request->user()->id,
                'book_id'       => $book_id,
                'start_time'    => $now,
                'status'        => ReadingHistory::STATUS_INPROGRESS,
                'historyData'   => [],
                'evaluationData'=> [],
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

        $history = ReadingHistory::updateOrCreate(
            [
                'inst'    => session('lib_inst'),
                'user_id' => session('uid'),
                'book_id' => $validated['book_id'],
            ],
            ['status' => $validated['status']]
        );

        if ($request->operation === 'create_history') {
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

    /**
     * reset reading history for a book
     */

    public function reset(Request $request, $book_id)
    {
        $inst = session('lib_inst');
        $uid  = session('uid');

        $history = ReadingHistory::where('inst', $inst)
            ->where('user_id', $uid)
            ->where('book_id', $book_id)
            ->first();

        if (!$history) {
            // Nothing to reset; return ok=false so caller can decide UI
            return response()->json(['success' => false, 'message' => 'Reading history not found'], 404);
        }

        // Normalize historyData to array
        $historyData = $history->historyData ?? [];
        if (is_string($historyData)) {
            $historyData = json_decode($historyData, true) ?: [];
        }

        // Blank section timing/status but keep structure (title/start/end/level/summary)
        foreach ($historyData as $idx => $section) {
            // Ensure array shape
            if (!is_array($section)) $section = [];

            $section['start_time'] = null;
            $section['end_time']   = null;
            // Use your canonical blank status; you’ve used STATUS_NONE and literal 'none' elsewhere.
            // Picking STATUS_NONE for consistency.
            $section['status']     = ReadingHistory::STATUS_NONE;

            $historyData[$idx] = $section;
        }

        // Also reset top-level reading state (optional but usually desired on a full reset)
        $history->start_time = null;
        $history->end_time   = null;
        $history->status     = ReadingHistory::STATUS_NONE;

        $history->historyData = $historyData;
        $history->save();

        return response()->json([
            'success' => true,
            'data'    => $history,
        ]);
    }


    public function section_set_status(Request $request, $book_id)
    {
        $validated = $request->validate([
            'idx'    => 'required|integer',
            'status' => 'required|string|in:none,in_progress,completed',
        ]);

        $history = ReadingHistory::where('inst', session('lib_inst'))
            ->where('user_id', session('uid'))
            ->where('book_id', $book_id)
            ->first();

        if (!$history) {
            return response()->json(['error' => 'Reading history not found'], 404);
        }

        $historyData = $history->historyData ?? [];
        if (is_string($historyData)) $historyData = json_decode($historyData, true) ?: [];

        // Update one section by index
        if (isset($historyData[$validated['idx']])) {
            $historyData[$validated['idx']]['status'] = $validated['status'];
            if ($validated['status'] === 'in_progress') {
                $historyData[$validated['idx']]['start_time'] = now()->toDateTimeString();
            } elseif ($validated['status'] === 'completed') {
                $historyData[$validated['idx']]['end_time'] = now()->toDateTimeString();
            } elseif ($validated['status'] === 'none') {
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

    /**
     * NEW: Save a section/page summary (by idx OR by start/end page range)
     * Route name suggestion: reading_history.section_summary.store
     */
    public function section_summary_store(Request $request, $book_id)
    {
        $validated = $request->validate([
            'summary' => 'required|string|max:20000',
            'idx'     => 'nullable|integer',
            'start'   => 'nullable|integer|min:1',
            'end'     => 'nullable|integer|min:1',
        ]);

        $inst = session('lib_inst');
        $uid  = session('uid');

        $history = ReadingHistory::where('inst', $inst)
            ->where('user_id', $uid)
            ->where('book_id', $book_id)
            ->first();

        if (!$history) {
            return response()->json(['error' => 'Reading history not found'], 404);
        }

        // Load book & ToC (for start/end → idx resolution)
        $book = Book::where('inst', $inst)->where('id', $book_id)->first();
        if (!$book) return response()->json(['error' => 'Book not found'], 404);
        $toc = [];
        if (!empty($book->auto_toc)) {
            $toc = is_array($book->auto_toc) ? $book->auto_toc : (json_decode($book->auto_toc, true) ?: []);
        }

        $historyData = $history->historyData ?? [];
        if (is_string($historyData)) $historyData = json_decode($historyData, true) ?: [];

        // Decide target index
        $targetIdx = $validated['idx'] ?? null;
        if ($targetIdx === null) {
            $start = $validated['start'] ?? null;
            $end   = $validated['end'] ?? $start;

            if ($start === null) {
                return response()->json(['error' => 'idx or start (and optional end) is required'], 422);
            }
            $targetIdx = $this->resolveSectionIndex($toc, $start, $end);
            if ($targetIdx === null) {
                return response()->json(['error' => 'Unable to resolve section index from page range'], 422);
            }
        }

        if (!isset($historyData[$targetIdx])) {
            // Initialize if missing
            $historyData[$targetIdx] = [
                'status'      => ReadingHistory::STATUS_NONE,
                'start_time'  => null,
                'end_time'    => null,
            ];
        }

        $historyData[$targetIdx]['summary'] = $validated['summary'];
        $historyData[$targetIdx]['summary_updated_at'] = now()->toDateTimeString();

        $history->historyData = $historyData;
        $history->save();

        return response()->json([
            'success' => true,
            'idx'     => $targetIdx,
            'summary' => $historyData[$targetIdx]['summary'],
        ]);
    }

    /**
     * NEW: Get a section/page summary (prefill)
     * Route name suggestion: reading_history.section_summary.get
     */
    public function section_summary_get(Request $request, $book_id)
    {
        $validated = $request->validate([
            'idx'   => 'nullable|integer',
            'start' => 'nullable|integer|min:1',
            'end'   => 'nullable|integer|min:1',
        ]);

        $inst = session('lib_inst');
        $uid  = session('uid');

        $history = ReadingHistory::where('inst', $inst)
            ->where('user_id', $uid)
            ->where('book_id', $book_id)
            ->first();

        if (!$history) {
            return response()->json(['summary' => null]); // no history yet, return empty
        }

        $historyData = $history->historyData ?? [];
        if (is_string($historyData)) $historyData = json_decode($historyData, true) ?: [];

        $targetIdx = $validated['idx'] ?? null;

        if ($targetIdx === null) {
            // Need to resolve by pages
            $book = Book::where('inst', $inst)->where('id', $book_id)->first();
            $toc  = [];
            if ($book && !empty($book->auto_toc)) {
                $toc = is_array($book->auto_toc) ? $book->auto_toc : (json_decode($book->auto_toc, true) ?: []);
            }
            $start = $validated['start'] ?? null;
            $end   = $validated['end'] ?? $start;

            if ($start === null) {
                return response()->json(['summary' => null]);
            }

            $targetIdx = $this->resolveSectionIndex($toc, $start, $end);
            if ($targetIdx === null) {
                return response()->json(['summary' => null]);
            }
        }

        $summary = $historyData[$targetIdx]['summary'] ?? null;

        return response()->json([
            'idx'     => $targetIdx,
            'summary' => $summary,
        ]);
    }

    /**
     * Helper: resolve a ToC index from page range
     */
    private function resolveSectionIndex(array $toc, int $start, ?int $end = null): ?int
    {
        $end = $end ?? $start;

        // 1) exact match on (start,end)
        foreach ($toc as $i => $sec) {
            $s = $sec['start'] ?? ($sec['page'] ?? null);
            $e = $sec['end']   ?? ($sec['page'] ?? null);
            if ($s !== null && $e !== null && (int)$s === (int)$start && (int)$e === (int)$end) {
                return (int)$i;
            }
        }

        // 2) range containment: section covers the requested range
        foreach ($toc as $i => $sec) {
            $s = $sec['start'] ?? ($sec['page'] ?? null);
            $e = $sec['end']   ?? ($sec['page'] ?? null);
            if ($s !== null && $e !== null && $s <= $start && $e >= $end) {
                return (int)$i;
            }
        }

        // 3) fallback: first section whose single page equals start
        foreach ($toc as $i => $sec) {
            $p = $sec['page'] ?? null;
            if ($p !== null && (int)$p === (int)$start) {
                return (int)$i;
            }
        }

        return null;
    }

    /**
     * AI explanation for a page range
     * Route name suggestion should match your blade: reading_history.section_ai
     */
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
            ->filter(fn($p) => ($p['page'] ?? 0) >= $start && ($p['page'] ?? 0) <= $end)
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
4. Output valid JSON only, in this structure:

{
  "explanation": "....",
  "questions": [
    {"q": "....?", "answer": true},
    {"q": "....?", "answer": false}
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

        $result  = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? null;

        $cleanJson = preg_replace('/^```json|```$/i', '', trim((string) $content));
        $data = json_decode(trim($cleanJson), true);

        return response()->json([
            'success'   => true,
            'book_id'   => $book_id,
            'start'     => $start,
            'end'       => $end,
            'meta_data' => $data
        ]);
    }

    public function show($id, Request $request)
    {
        // --- Institution/session setup (same as BookController) ---
        if (session()->has('lib_inst')) {
            $theInst = new \vwmldbm\code\Inst_var(null, session('lib_inst'));
        } elseif (!Auth::check()) {
            if (config('app.multi_inst', '')) { // multi-inst mode
                if (!session()->has('lib_inst')) {
                    return view('auth.inst'); // user must select institution
                }
            } else {
                session(['lib_inst' => config('app.inst', 1)]);
            }
        } elseif (session()->has('lib_inst')) {
            $theInst = new \vwmldbm\code\Inst_var(null, session('lib_inst'));
            session(['inst_uname' => $theInst->inst_uname]);
        }

        // --- Make available for legacy libs (pdf.js etc.) ---
        $_SESSION['inst']      = session('lib_inst');
        $_SESSION['app.root']  = config('app.root');
        $_SESSION['app.root2'] = config('app.root2');
        $_SESSION['app.url']   = config('app.url');

        // --- Find the book ---
        $book = Book::where('inst', session('lib_inst'))
            ->where('id', $id)
            ->first();

        if (!$book) {
            return redirect('/book/')->with('warning', __("The resource does not exist!"));
        }

        // --- Find reading history for this user/book ---
        $readingHistory = ReadingHistory::where('inst', session('lib_inst'))
            ->where('user_id', session('uid'))
            ->where('book_id', $book->id)
            ->first();

        // If the user clicked a "start reading" action
        if ($request->input('reading_action') === "START_READING") {
            $readingHistory = ReadingHistory::firstOrCreate(
                [
                    'inst'    => session('lib_inst'),
                    'user_id' => session('uid'),
                    'book_id' => $book->id,
                ],
                [
                    'status'     => ReadingHistory::STATUS_INPROGRESS,
                    'start_time' => now(),
                ]
            );
        }

        // --- Decode book ToC and match with history data ---
        $toc = [];
        if (!empty($book->auto_toc)) {
            $toc = is_array($book->auto_toc) ? $book->auto_toc : (json_decode($book->auto_toc, true) ?: []);
        }

        $historyData = $readingHistory ? $readingHistory->historyData : [];
        if (is_string($historyData)) $historyData = json_decode($historyData, true) ?: [];

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
                'summary'    => $sectionHistory['summary'] ?? null,
            ];
        }

        // --- Return view ---
        return view('reading_history.show')
            ->with('book', $book)
            ->with('readingHistory', $readingHistory)
            ->with('sections', $sections);
    }
}
