<?php

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/registration/userInfo', function (){

});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/after/login', 'BookController@getStarted');

Route::get('/after/register', 'BookController@getStarted');

Route::post('/add/mylibrary', 'LibraryController@store');
Route::get('/mylibrary/destroy/{bookId}', 'LibraryController@destroy');

Route::post('/add/myreading', 'MyReadingController@store');
Route::get('/myreading/destroy/{bookId}', 'MyReadingController@destroy');

Route::post('/add/wishlist', 'WishListController@store');
Route::get('/wishlist/destroy/{bookId}', 'WishListController@destroy');

Route::get('/all/review/{bookId}', 'ReviewController@allReview');
Route::post('add/review', 'ReviewController@store');
Route::get('/review/destroy/{bookId}', 'ReviewController@destroy');

Route::get('/average/rating/{bookId}', 'RatingController@averageRating');
Route::get('/rating/{bookId}', 'RatingController@allRating');
Route::post('/add/rating', 'RatingController@store');
Route::get('/rating/destroy/{bookId}', 'RatingController@destroy');
