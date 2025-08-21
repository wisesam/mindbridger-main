<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\BookTextMeta; 

class BookTextMetaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function extract_meta($id, Request $request)
    {
        $inst = session('lib_inst');

        // Get the text JSON from the request
        $pdfTextJson = $request->input('pdf_text');
        $pdfTextArr = json_decode($pdfTextJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($pdfTextArr)) {
            return response()->json(['error' => 'Invalid pdf_text JSON provided.'], 400);
        }

        // Concatenate pages into a single text snippet (limit length for API cost/performance)
        $combinedText = collect($pdfTextArr)
            ->take(10) // use only first 10 pages to save tokens
            ->pluck('text')
            ->implode("\n\n");

        $locale = app()->getLocale();

        // Build prompt for OpenAI
        $prompt = <<<EOT
    You are a helpful assistant that extracts structured metadata from a book's text.

    Given the following excerpt from a book, generate a JSON object with these fields:
    - title
    - author
    - genre
    - category
    - difficulty (elementary, middle, high school, college)
    - theme
    - summary (7-10 sentences)
    
    The output language should be {$locale}
    Respond ONLY with valid JSON.

    Excerpt:
    {$combinedText}
    EOT;

        // Call OpenAI API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini', // lighter, faster model for extraction
            'messages' => [
                ['role' => 'system', 'content' => 'You are a book metadata extractor.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to connect to OpenAI API.'], 500);
        }

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return response()->json(['error' => 'No response from OpenAI.'], 500);
        }

        // Clean response (strip markdown fences if present)
        $cleanJson = preg_replace('/^```json|```$/i', '', trim($content));
        $meta = json_decode(trim($cleanJson), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'OpenAI returned invalid JSON.',
                'raw_response' => $content
            ], 500);
        }

        // Save into BookTextMeta (insert or update)
        $record = BookTextMeta::updateOrCreate(
            ['book_id' => $id, 'inst' => $inst],
            ['meta_data' => json_encode($meta, JSON_PRETTY_PRINT)]
        );

        return response()->json([
            'success' => true,
            'book_id' => $id,
            'meta_data' => $meta,
        ]);
    }

    public function get_meta(Request $request, $book_id)
    {
        // Find existing meta
        $meta = BookTextMeta::where('book_id', $book_id)->where('inst',session('lib_inst'))->first();

        if (empty($meta->meta)) {
            // Return one message only
            return response()->json([
                'message' => __('Not Available')
            ]);
        }
        
        $data = json_decode($meta->meta, true);
        return response()->json([
            'title'      => $data['title'] ?? null,
            'author'     => $data['author'] ?? null,
            'genre'      => $data['genre'] ?? null,
            'category'   => $data['category'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'theme'      => $data['theme'] ?? null,
            'summary'    => $data['summary'] ?? null,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function chapter_txt($id, Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
