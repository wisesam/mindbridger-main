<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth; // [SJH]
use App\Libraries\Code; // [SJH]
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class BookAIAdvisorController extends Controller
{
    public function recommend(Request $request)
    {
        $age = $request->input('age');
        $grade = $request->input('grade');
        $interest = $request->input('interest');
        $isFree = $request->input('free') ? 'free' : 'paid or free';

        // model 
            $model = 'gpt-3.5-turbo'; // or 'gpt-4o' 
        
        // $prompt = "Recommend 3 {$isFree} e-books for a grade {$grade} student, age {$age}, interested in {$interest}. Include title, author, and a short reason for each.";
         $gradeTxt = $grade ? " grade {$grade}" :null;
         $prompt = "Recommend 3 {$isFree} e-books for a {$gradeTxt} student, age {$age}, interested in {$interest}. 
            Respond in JSON format as an array of objects, each with 'title', 'author', and 'reason'.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful book recommender.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        // dd($result);  // for debugging

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to get response from AI'], 500);
        }

        $result = $response->json(); // associative array

        $content = $result['choices'][0]['message']['content'];
        $cleanJson = trim($content);
        $cleanJson = preg_replace('/^```json|```$/i', '', $cleanJson); // remove backticks
        $cleanJson = trim($cleanJson);

        $recommendedBooks = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($recommendedBooks)) {
            return back()->withErrors(['error' => 'Failed to parse AI response.'])->with('response', $response);
        }

        return view('recommendations.result', compact('recommendedBooks'))->with('response', $response);
    }

    public function form()
    {
        return view('recommendations.form');
    }
}

