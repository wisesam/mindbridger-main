<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use App\Libraries\Code;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use App\Models\Book;

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class BookAIAdvisorController extends Controller
{
    /**
     * Local-first recommendations (up to 5), then top up with OpenAI.
     * If "free" is checked, only show locally free eBooks and ask AI for free ones.
     */
    public function recommend(Request $request)
    {
        $validated = $request->validate([
            'age'      => 'required|integer|min:1|max:120',
            'grade'    => 'nullable|string|max:50',
            'interest' => 'required|string|max:255',
            'free'     => 'nullable|boolean',
        ]);

        $age      = (int) $validated['age'];
        $grade    = $validated['grade'] ?? '';
        $interest = $validated['interest'];
        $freeOnly = $request->boolean('free');
        $target   = 5;

        $inst = session('lib_inst') ?? config('app.inst', 1);

        // -------------------------------
        // 1) LOCAL SEARCH
        // -------------------------------
        $tokens = collect(preg_split('/[,\s]+/u', $interest, -1, PREG_SPLIT_NO_EMPTY))
                    ->unique()
                    ->take(8);

        $q = \App\Models\Book::query()->where('inst', $inst);

        // if ($grade !== '') {
        //     $q->where('c_grade', $grade);
        // }

        if ($tokens->isNotEmpty()) {
            $q->where(function ($qq) use ($tokens) {
                foreach ($tokens as $t) {
                    $like = "%{$t}%";
                    $qq->orWhere('title', 'LIKE', $like)
                    ->orWhere('author', 'LIKE', $like)
                    ->orWhere('keywords', 'LIKE', $like)
                    ->orWhere('c_genre', 'LIKE', $like)
                    ->orWhere('c_category', 'LIKE', $like)
                    ->orWhere('c_category2', 'LIKE', $like);
                }
            });
        }

        // Free-only: check price only
        // if ($freeOnly) {
        //     $q->where(function ($qq) {
        //         $qq->whereNull('price')
        //         ->orWhere('price', 0)
        //         ->orWhere('price', '0');
        //     });
        // }

        $local = $q->orderByDesc('id')
            ->limit($target)
            ->get(['id','title','author','price','url']);

        $recommendedBooks = $local->map(function ($b) {
            return [
                'title'   => $b->title,
                'author'  => $b->author,
                'reason'  => __('Available in our library. Matches your interests.'),
                'link'    => route('book.show', ['book' => $b->id]),
                'source'  => 'local',
            ];
        })->values()->all();

        // -------------------------------
        // 2) AI TOP-UP
        // -------------------------------
        $needed   = max(0, $target - count($recommendedBooks));
        $aiExtras = [];
        $apiRaw   = null;

        if ($needed > 0) {
            $model = 'gpt-4o-mini';
            $gradeTxt = $grade !== '' ? "grade {$grade}" : "an appropriate grade";
            
            $locale = app()->getLocale();

            $freePromptPart = $freeOnly
                ? "that are LEGALLY FREE to read/download online and include a valid official free URL for each"
                : "that may be paid or free (prefer widely available titles) and include a relevant public URL for each if possible";

            $prompt = "Recommend {$needed} e-books {$freePromptPart} for a {$gradeTxt} student, age {$age}, interested in {$interest} in {$locale} language.
    
    Respond ONLY with a raw JSON array (no prose, no backticks). Each object must have:
    - title (string)
    - author (string)
    - reason (string; 12–25 words) in {$locale} language
    - url (string; if free-only, must be a direct official/free reading page. Make sure it is a valid URL!)
    ";

            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a precise book recommender that returns strict JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.4,
                ]);

                if ($response->ok()) {
                    $apiRaw = $response->json();
                    $content = data_get($apiRaw, 'choices.0.message.content', '[]');
                    $clean = trim(preg_replace('/^```json|```$/i', '', trim($content)));
                    $parsed = json_decode($clean, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                        $aiExtras = collect($parsed)->map(function ($x) use ($freeOnly) {
                            $title  = trim($x['title']  ?? '');
                            $author = trim($x['author'] ?? '');
                            $reason = trim($x['reason'] ?? '');
                            $url    = trim($x['url']    ?? '');

                            if (!$url && !$freeOnly && $title) {
                                $q = urlencode($title . ' ' . $author);
                                $url = "https://www.google.com/search?q={$q}";
                            }

                            return [
                                'title'   => $title,
                                'author'  => $author,
                                'reason'  => $reason ?: __('Recommended based on your interests.'),
                                'link'    => $url ?: null,
                                'source'  => 'ai',
                            ];
                        })->all();
                    }
                }
            } catch (\Throwable $e) {
                $apiRaw = ['error' => $e->getMessage()];
            }
        }

        $recommendedBooks = array_slice(array_merge($recommendedBooks, $aiExtras), 0, $target);

        return view('recommendations.result', [
            'recommendedBooks' => $recommendedBooks,
            'response'         => $apiRaw,
        ]);
    }

    public function form()
    {
        return view('recommendations.form');
    }

    // Keeps your placeholder meta endpoint as-is
    public function auto_meta($book_id, Request $request)
    {
        $inst=session('lib_inst');
        $book = Book::where("inst",$inst)->where("id",$book_id)->firstOrFail();

        $text = $request->input('text'); // text of 10 pages or specific section
        if(!empty($text)) {
             return response()->json([
                'auto_meta' => [
                    ['summary' => $text],
                ],
            ]);
        } else return response()->json(['auto_meta' => 'No text provided for analysis.']);
    }
}
