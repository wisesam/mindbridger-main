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
            'age'           => 'required|integer|min:1|max:120',
            'grade'         => 'nullable|string|max:50',
            'interest'      => 'required|string|max:255',
            'book_language' => 'nullable|string',
            'free'          => 'nullable|boolean',
        ]);

        $age      = (int) $validated['age'];
        $grade    = $validated['grade'] ?? '';
        $interest = $validated['interest'];
        $freeOnly = $request->boolean('free');
        $target   = 5;

        $bookLanguage = $validated['book_language'] ?? null;

        $bookLanguages = config('book_languages');

        $bookLanguageName = $bookLanguage
            ? ($bookLanguages[$bookLanguage] ?? null)
            : null;

        $inst = session('lib_inst') ?? config('app.inst', 1);

// -------------------------------
// 1) LOCAL SEARCH
// -------------------------------

    $recommendedBooks = [];

    // "Free E-books Only" means:
    // legally free ebooks accessible on the public internet.
    // Therefore, skip local-library recommendations when enabled.
    if (!$freeOnly) {

        $tokens = collect(
            preg_split('/[,\s]+/u', $interest, -1, PREG_SPLIT_NO_EMPTY)
        )
            ->unique()
            ->take(8);

        $q = \App\Models\Book::query()
            ->where('inst', $inst);

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

        $local = $q->orderByDesc('id')
            ->limit($target)
            ->get([
                'id',
                'title',
                'author',
                'price',
                'url'
            ]);

        $recommendedBooks = $local->map(function ($b) {

            return [
                'title'  => $b->title,
                'author' => $b->author,
                'reason' => __('Available in our library. Matches your interests.'),
                'link'   => route('book.show', ['book' => $b->id]),
                'source' => 'local',
            ];

        })->values()->all();
    }

// -------------------------------
// 2) AI / WEB RECOMMENDATIONS
// -------------------------------
    $needed   = max(0, $target - count($recommendedBooks));
    $aiExtras = [];
    $apiRaw   = null;

    if ($needed > 0) {

        // Good default for this task.
        $model = 'gpt-5.6-luna';


        // -------------------------------------------------
        // Student grade
        // -------------------------------------------------
        $gradeTxt = $grade !== ''
            ? "grade {$grade}"
            : "an appropriate grade";


        // -------------------------------------------------
        // Interface / explanation language
        // This is completely separate from book language.
        // -------------------------------------------------
        $locale = app()->getLocale();

        $languageName = match ($locale) {
            'ko' => 'Korean',
            'en' => 'English',
            'id' => 'Indonesian',
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
            'ru' => 'Russian',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'fa' => 'Persian',
            'ur' => 'Urdu',
            'km' => 'Khmer',
            'lo' => 'Lao',
            default => 'English',
        };


        // -------------------------------------------------
        // Book language instruction
        // $bookLanguageName comes from config/book_languages.php
        //
        // NULL = any language
        // -------------------------------------------------
        if ($bookLanguageName) {

            $bookLanguageInstruction = <<<TXT
    The ebook itself MUST be available in {$bookLanguageName}.

    Important:
    - Verify that the specific linked ebook edition is actually in {$bookLanguageName}.
    - Do NOT recommend a different-language edition.
    - Do NOT recommend a book merely because a {$bookLanguageName} translation exists somewhere.
    - The supplied URL must point to the {$bookLanguageName} edition, or to a page clearly offering that edition.
    - If the requested language cannot be verified, omit the book.
    - Return exactly "{$bookLanguageName}" in the "language" field.
    TXT;

        } else {

            $bookLanguageInstruction = <<<TXT
    The ebook may be in any language.

    Important:
    - Verify the actual language of the specific linked ebook edition.
    - Return the actual language of that edition in the "language" field.
    TXT;
        }


        // -------------------------------------------------
        // Normalize URL so we can compare AI output with
        // actual URLs returned by OpenAI web search.
        // -------------------------------------------------
        $normalizeUrl = function (?string $url): ?string {

            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                return null;
            }

            $parts = parse_url($url);

            if (empty($parts['host'])) {
                return null;
            }

            $host = strtolower($parts['host']);
            $host = preg_replace('/^www\./', '', $host);

            $path = $parts['path'] ?? '/';
            $path = rtrim($path, '/');

            if ($path === '') {
                $path = '/';
            }

            $query = isset($parts['query'])
                ? '?' . $parts['query']
                : '';

            // Scheme deliberately ignored.
            return $host . $path . $query;
        };


        // -------------------------------------------------
        // Second safety check:
        // make sure the URL is actually reachable.
        // -------------------------------------------------
        $urlIsReachable = function (?string $url): bool {

            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }

            $scheme = strtolower(
                parse_url($url, PHP_URL_SCHEME) ?? ''
            );

            if (!in_array($scheme, ['http', 'https'], true)) {
                return false;
            }

            try {

                $response = \Illuminate\Support\Facades\Http::timeout(6)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 MindBridgerLibrary/1.0',
                    ])
                    ->withOptions([
                        'allow_redirects' => [
                            'max' => 5,
                            'strict' => false,
                        ],
                    ])
                    ->head($url);


                if ($response->successful()) {
                    return true;
                }


                // Some legitimate sites reject HEAD but accept GET.
                if (in_array($response->status(), [403, 405], true)) {

                    $response = \Illuminate\Support\Facades\Http::timeout(6)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 MindBridgerLibrary/1.0',
                            'Range'      => 'bytes=0-1024',
                        ])
                        ->withOptions([
                            'allow_redirects' => [
                                'max' => 5,
                                'strict' => false,
                            ],
                            'stream' => true,
                        ])
                        ->get($url);

                    return $response->successful();
                }

            } catch (\Throwable $e) {

                return false;
            }

            return false;
        };


        // -------------------------------------------------
        // Free / normal ebook instruction
        // -------------------------------------------------
        if ($freeOnly) {

            $availabilityInstruction = <<<TXT
    Only recommend ebooks that are LEGALLY FREE to read in full online.

    Important:
    - You MUST use web search before recommending each book.
    - Never invent, reconstruct, or guess a URL.
    - The URL must come from a web-search result you actually found.
    - The full ebook must genuinely be available without payment.
    - Prefer stable sources such as Project Gutenberg, Standard Ebooks,
    official publishers, universities, government organizations,
    educational organizations, Internet Archive when appropriate,
    or other legitimate full-text providers.
    - A preview, sample, review, Amazon listing, Goodreads page,
    search-result page, or bookstore product page DOES NOT count
    as a free ebook.
    - If you cannot find a verified legal full-text page,
    DO NOT include that book.
    - It is acceptable to return fewer than {$needed} books.
    TXT;

        } else {

            $availabilityInstruction = <<<TXT
    Recommend ebooks that may be paid or free.

    Important:
    - You MUST use web search before recommending each book.
    - Never invent, reconstruct, or guess a URL.
    - The URL must come from a web-search result you actually found.
    - Prefer stable canonical pages such as:
    * publisher book pages
    * Google Books
    * Open Library
    * Project Gutenberg
    * Standard Ebooks
    * institutional or official book pages
    - Avoid Amazon and other retailer deep links because they frequently
    change by country, edition, and availability.
    - If a reliable direct book page cannot be found, omit the book.
    TXT;
        }


    // -------------------------------------------------
    // Main prompt
    // -------------------------------------------------
    $requestedBookLanguage = $bookLanguageName ?: 'Any language';
    $prompt = <<<PROMPT
    Find up to {$needed} ebooks for the following student.

    Student:
    - Age: {$age}
    - School level: {$gradeTxt}
    - Interest: {$interest}
    - Recommendation explanation language: {$languageName}
    - Requested ebook language: {$requestedBookLanguage}

    BOOK LANGUAGE REQUIREMENTS:

    {$bookLanguageInstruction}


    AVAILABILITY REQUIREMENTS:

    {$availabilityInstruction}


    Recommendation requirements:

    1. The book must actually exist.

    2. Confirm the title and author using web search.

    3. Verify the language of the specific linked ebook edition.

    4. Recommend books appropriate for the student's age
    and school level.

    5. Match the student's stated interest as closely as possible.

    6. Write the recommendation reason in {$languageName}.

    7. Keep each recommendation reason approximately 12-25 words.

    8. Copy the discovered URL exactly.
    Never manufacture, construct, or guess a URL.

    9. Prefer a stable book landing page or reading page
    rather than a temporary download URL.

    10. Do not include duplicate titles.

    11. If the language of the linked edition cannot be verified,
        omit the book rather than guessing.

    12. The "language" field must describe the language of the
        ACTUAL LINKED EBOOK, not the interface language.

    For translated editions:
    - If a book language was requested, use that edition only
    when the edition can be verified.
    - Use the title of the requested-language edition when
    that translated title can be verified.
    - Never invent a translated title.
    - Verify that the supplied URL actually corresponds to
    the requested-language edition.
    PROMPT;


        try {

            $response = \Illuminate\Support\Facades\Http::withToken(
                    env('OPENAI_API_KEY')
                )
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', [

                    'model' => $model,

                    'reasoning' => [
                        'effort' => 'low',
                    ],

                    'instructions' =>
                        'You are a careful educational ebook recommender. '
                        . 'Accuracy, correct book language, and valid links '
                        . 'are more important than filling the requested '
                        . 'number of recommendations.',

                    'input' => $prompt,


                    // -----------------------------------------
                    // Force live web search
                    // -----------------------------------------
                    'tools' => [
                        [
                            'type' => 'web_search',

                            'external_web_access' => true,

                            'search_context_size' => 'medium',

                            // Avoid unstable retailer/product URLs
                            'filters' => [
                                'blocked_domains' => [
                                    'amazon.com',
                                    'amazon.co.uk',
                                    'amazon.co.jp',
                                    'amazon.de',
                                    'amazon.fr',
                                    'amazon.ca',
                                    'amazon.com.au',
                                    'amazon.in',
                                    'ebay.com',
                                    'abebooks.com',
                                    'goodreads.com',
                                ],
                            ],
                        ],
                    ],

                    'tool_choice' => 'required',


                    // -----------------------------------------
                    // Keep source URLs returned by web search
                    // -----------------------------------------
                    'include' => [
                        'web_search_call.action.sources',
                    ],


                    // -----------------------------------------
                    // Structured output
                    // -----------------------------------------
                    'text' => [
                        'format' => [
                            'type'   => 'json_schema',
                            'name'   => 'ebook_recommendations',
                            'strict' => true,

                            'schema' => [
                                'type' => 'object',

                                'properties' => [

                                    'books' => [
                                        'type'     => 'array',
                                        'minItems' => 0,
                                        'maxItems' => $needed,

                                        'items' => [
                                            'type' => 'object',

                                            'properties' => [

                                                'title' => [
                                                    'type' => 'string',
                                                ],

                                                'author' => [
                                                    'type' => 'string',
                                                ],

                                                'reason' => [
                                                    'type' => 'string',
                                                ],

                                                'url' => [
                                                    'type' => 'string',
                                                ],

                                                'source_name' => [
                                                    'type' => 'string',
                                                ],

                                                // If a specific language was selected,
                                                // force exact language name.
                                                'language' => $bookLanguageName
                                                    ? [
                                                        'type' => 'string',
                                                        'enum' => [
                                                            $bookLanguageName
                                                        ],
                                                    ]
                                                    : [
                                                        'type' => 'string',
                                                    ],
                                            ],

                                            'required' => [
                                                'title',
                                                'author',
                                                'reason',
                                                'url',
                                                'source_name',
                                                'language',
                                            ],

                                            'additionalProperties' => false,
                                        ],
                                    ],
                                ],

                                'required' => [
                                    'books',
                                ],

                                'additionalProperties' => false,
                            ],
                        ],
                    ],

                    'max_output_tokens' => 2500,
                ]);


            if ($response->successful()) {

                $apiRaw = $response->json();


                // -------------------------------------------------
                // 1. Collect URLs that OpenAI actually found
                //    during web search
                // -------------------------------------------------
                $searchedUrls = collect();

                foreach (
                    data_get($apiRaw, 'output', [])
                    as $item
                ) {

                    if (($item['type'] ?? '') !== 'web_search_call') {
                        continue;
                    }

                    foreach (
                        data_get($item, 'action.sources', [])
                        as $source
                    ) {

                        $sourceUrl = trim(
                            $source['url'] ?? ''
                        );

                        if ($sourceUrl) {

                            $normalized = $normalizeUrl(
                                $sourceUrl
                            );

                            if ($normalized) {
                                $searchedUrls->push($normalized);
                            }
                        }
                    }
                }

                $searchedUrls = $searchedUrls
                    ->unique()
                    ->values();


                // -------------------------------------------------
                // 2. Extract structured model output
                // -------------------------------------------------
                $content = null;

                foreach (
                    data_get($apiRaw, 'output', [])
                    as $item
                ) {

                    if (($item['type'] ?? '') !== 'message') {
                        continue;
                    }

                    foreach (
                        ($item['content'] ?? [])
                        as $part
                    ) {

                        if (
                            ($part['type'] ?? '')
                            === 'output_text'
                        ) {

                            $content = $part['text'] ?? null;

                            break 2;
                        }
                    }
                }


                // -------------------------------------------------
                // 3. Parse recommendations
                // -------------------------------------------------
                if ($content) {

                    $parsed = json_decode(
                        $content,
                        true
                    );

                    if (
                        json_last_error() === JSON_ERROR_NONE
                        && is_array($parsed)
                    ) {

                        $books = $parsed['books'] ?? [];


                        $aiExtras = collect($books)

                            ->map(function ($x) use (
                                $freeOnly,
                                $searchedUrls,
                                $normalizeUrl,
                                $urlIsReachable,
                                $bookLanguage,
                                $bookLanguageName
                            ) {

                                $title = trim(
                                    $x['title'] ?? ''
                                );

                                $author = trim(
                                    $x['author'] ?? ''
                                );

                                $reason = trim(
                                    $x['reason'] ?? ''
                                );

                                $url = trim(
                                    $x['url'] ?? ''
                                );

                                $language = trim(
                                    $x['language'] ?? ''
                                );


                                if (!$title) {
                                    return null;
                                }


                                // -----------------------------------------
                                // URL must actually have been returned
                                // by the web-search tool
                                // -----------------------------------------
                                $normalizedUrl = $normalizeUrl($url);

                                $foundBySearch =
                                    $normalizedUrl
                                    && $searchedUrls->contains(
                                        $normalizedUrl
                                    );


                                // -----------------------------------------
                                // Check whether page currently responds
                                // -----------------------------------------
                                $reachable =
                                    $foundBySearch
                                    && $urlIsReachable($url);


                                // -----------------------------------------
                                // Reject / fallback broken URLs
                                // -----------------------------------------
                                if (
                                    !$foundBySearch
                                    || !$reachable
                                ) {

                                    if ($freeOnly) {

                                        // For free ebooks, never use a
                                        // search page as substitute.
                                        return null;

                                    } else {

                                        // For normal recommendations,
                                        // use Google Books search as a
                                        // stable fallback.

                                        $searchText =
                                            trim(
                                                $title
                                                . ' '
                                                . $author
                                                . (
                                                    $bookLanguageName
                                                    ? ' ' . $bookLanguageName
                                                    : ''
                                                )
                                            );

                                        $q = rawurlencode(
                                            $searchText
                                        );

                                        $url =
                                            "https://books.google.com/books?q={$q}";
                                    }
                                }


                                return [
                                    'title'    => $title,

                                    'author'   => $author,

                                    'reason'   => $reason
                                        ?: __(
                                            'Recommended based on your interests.'
                                        ),

                                    'language' => $language,

                                    'link'     => $url,

                                    'source'   => 'ai',
                                ];

                            })

                            ->filter()

                            ->values()

                            ->all();
                    }
                }

            } else {

                $apiRaw = [
                    'status' => $response->status(),
                    'error'  => $response->body(),
                ];
            }

        } catch (\Throwable $e) {

            $apiRaw = [
                'error' => $e->getMessage(),
            ];
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
