<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination;
use App\Book; 
use App\Models\BookUserFavorite;
use DB; // instead of Eloquent, use DB

require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");
require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root')."/app/Libraries/book.php");

class BookUserFavoriteController extends Controller
{
    // Show user's favorite books
    public function index(Request $request)
    {
        $favorites = BookUserFavorite::where('user_id', $request->user()->id)
        ->where('inst', $request->user()->inst)
        ->pluck('book_id');

        if($request->input('page_size'))
            $pageSize=$request->input('page_size');
        else $pageSize=10;

        $books = Book::where('inst', $request->user()->inst)
        ->whereIn('id', $favorites)->orderBy('id','desc')->paginate($pageSize);

        return view('book.favorite')->with('books', $books);
    }

    // Count favorite by user
    public function count(Request $request)
    {
        $favorites = BookUserFavorite::where('user_id', $request->user()->id)
        ->where('inst', $request->user()->inst)
        ->count();
        return response()->json(['count' => $count]);
    }

    // Check if the book is in favoriate list
    public function check(Request $request, $book_id)
    {
        $exists = \App\Models\BookUserFavorite::where('inst', $request->user()->inst)
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book_id)
            ->exists();

        return response()->json(['favorited' => $exists]);
    }

    // Add a book to favorites
    public function store(Request $request, $book_id)
    {
        BookUserFavorite::firstOrCreate([
            'inst' => $request->user()->inst,
            'user_id' => $request->user()->id,
            'book_id' => $book_id,
        ]);

        return response()->json(['message' => 'Book favorited']);
    }


    // Remove a book from favorites
    public function destroy(Request $request, $book_id)
    {
        $deleted = BookUserFavorite::where('inst', $request->user()->inst)
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book_id)
            ->delete();

        return response()->json(['message' => 'Book removed from favorites', 'deleted' => $deleted]);
    }
}
