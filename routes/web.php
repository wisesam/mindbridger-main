<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RootController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Book_copyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BookUserFavoriteController;
use App\Http\Controllers\BookUserEshelfController;
use App\Http\Controllers\BookAIAdvisorController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('locale/{locale}', function ($locale) {
    Session::put('locale', $locale);
	return redirect()->back();
});

Route::get('/',[RootController::class, 'index']);
Route::get('/wv2login',[RootController::class,'wv2login']);

Route::get('/inst',[RootController::class,'inst']);
// Route::get('/inst/{inst_uname}',['uses' => 'RootController@inst_uname']); // 6.2 version
Route::get('/inst/{inst_uname}', [RootController::class, 'inst_uname']);
Route::get('/inst/{inst_uname}/book/{book}',[RootController::class,'inst_uname_book']);
// Route::post('/inst_process',['as' =>'inst', 'uses' =>  [RootController::class,'inst_process']]); // 6.2 version
Route::post('/inst_process', [RootController::class, 'inst_process'])->name('inst');
Route::get('/login/clear',[LoginController::class,'login_clear'])->name('login_clear'); // clear institution info first

Route::get('/about',[AboutController::class, 'about']);
Route::get('/about/edit', [AboutController::class, 'edit'])->name('about.edit'); // [SJH]
Route::patch('/about/update', [AboutController::class, 'update'])->name('about.update'); // [SJH]

// Auth::routes(['register'=>false]); // [SJH]

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');
// Route::get('/admin/dashboard', 'AdminController@index');

Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // [SJH]
Route::patch('users/{user}/update', [UserController::class, 'update'])->name('users.update'); // [SJH]
Route::delete('users/{user}/destroy', [UserController::class, 'destroy'])->name('users.destroy'); // [SJH]
Route::get('users/list', [UserController::class, 'list'])->name('users.list'); // [SJH]
Route::get('users/choose_list', [UserController::class, 'choose_list'])->name('users.choose_list'); // [SJH]

// Route::resource('inst/{inst}/book','BookController'); // [SJH]
Route::resource('book', BookController::class); // [SJH]
Route::resource('book_copy', Book_copyController::class)
    ->except(['create'])
    ->names([
        'create' => 'book_copy.create',
    ]); // [SJH] 2024.12.19

// Book Copy Specific Routes
Route::get('/book_copy/create/{book}', [Book_copyController::class, 'create'])->name('book_copy.create'); // [SJH] 2024.12.19

// Book Advanced Search
Route::get('/asearch', [BookController::class, 'asearch'])->name('book.asearch'); // [SJH]

// Rental Specific Routes
// Route::resource('rental', RentalController::class); // [SJH] 2024.12.19
Route::get('rental/create/{book_copy}', [RentalController::class, 'create'])->name('rental.create'); // [SJH]
Route::get('rental/{user}/all', [RentalController::class, 'list'])->name('rental.user.all'); // [SJH]
Route::get('rental/{user}/rented', [RentalController::class, 'rented_list'])->name('rental.user.rented'); // [SJH]
Route::get('rental/book_copy/{book_copy}/all', [RentalController::class, 'list_by_book_copy'])->name('rental.book_copy.all'); // [SJH]
Route::get('rental/book_copy/{book_copy}/rented', [RentalController::class, 'rented_list_by_book_copy'])->name('rental.book_copy.rented'); // [SJH]

Route::resource('announcement', AnnouncementController::class);

Route::middleware(['auth'])->group(function () {
    Route::get('favorite', [BookUserFavoriteController::class, 'index'])->name('book.favorite');
    Route::post('book/{book}/favorite', [BookUserFavoriteController::class, 'store'])->name('book.favorite.store');
    Route::get('book/{book}/favorite', [BookUserFavoriteController::class, 'check'])->name('book.favorite.check');
    Route::get('book/{book}/favorite_count', [BookUserFavoriteController::class, 'count'])->name('book.favorite.count');
    Route::delete('book/{book}/favorite', [BookUserFavoriteController::class, 'destroy'])->name('book.favorite.remove');
    Route::get('book/{book}/auto_toc', [BookController::class, 'auto_toc'])->name('book.auto_toc');
    Route::get('eshelf', [BookUserEshelfController::class, 'index'])->name('book.eshelf');
    Route::post('book/{book}/eshelf', [BookUserEshelfController::class, 'store'])->name('book.eshelf.store');
    Route::get('book/{book}/eshelf', [BookUserEshelfController::class, 'check'])->name('book.eshelf.check');
    Route::delete('book/{book}/eshelf', [BookUserEshelfController::class, 'destroy'])->name('book.eshelf.remove');
});


// AI Book Recommendation Routes
Route::get('/recommend', [BookAIAdvisorController::class, 'form'])->name('recommend.form');
Route::post('/recommend', [BookAIAdvisorController::class, 'recommend'])->name('recommend');

// cache clear by url [SJH]
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});