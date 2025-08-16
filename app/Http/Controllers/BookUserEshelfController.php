<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination;
use App\Models\Book; 
use App\Models\BookUserEshelf;
use DB; // instead of Eloquent, use DB

require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");
require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root')."/app/Libraries/book.php");

class BookUserEshelfController extends Controller
{
    // Show user's favorite books
    public function index(Request $request)
    {
        $eshelfList = BookUserEshelf::where('user_id', $request->user()->id)
        ->where('inst', $request->user()->inst)
        ->pluck('book_id');

        if($request->input('page_size'))
            $pageSize=$request->input('page_size');
        else $pageSize=10;

        $books = Book::where('inst', $request->user()->inst)
        ->whereIn('id', $eshelfList)->orderBy('id','desc')->paginate($pageSize);

        return view('book.eshelf')->with('books', $books);
    }

    // Count eshelf by user
    public function count(Request $request)
    {
        $count = BookUserEshelf::where('user_id', $request->user()->id)
        ->where('inst', $request->user()->inst)
        ->count();
        return response()->json(['count' => $count]);
    }

    // Check if the book is in favoriate list
    public function check(Request $request, $book_id)
    {
        $exists = BookUserEshelf::where('inst', $request->user()->inst)
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book_id)
            ->exists();

        return response()->json(['isMyEshelf' => $exists]);
    }

    // Add a book to favorites
    public function store(Request $request, $book_id)
    {
        BookUserEshelf::firstOrCreate([
            'inst' => $request->user()->inst,
            'user_id' => $request->user()->id,
            'book_id' => $book_id,
        ]);

        return response()->json(['message' => 'Book added to my eshelf']);
    }


    // Remove a book from favorites
    public function destroy(Request $request, $book_id)
    {
        $deleted = BookUserEshelf::where('inst', $request->user()->inst)
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book_id)
            ->delete();

        return response()->json(['message' => 'Book removed from my eshelf', 'deleted' => $deleted]);
    }
}
