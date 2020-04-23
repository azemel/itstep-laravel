<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
})->name("index");


Route::get("/books/new", "BooksController@create")->name("books.new");
Route::post("/books/new", "BooksController@add")->name("books.add");

Route::get("/books/{book}/edit", "BooksController@edit")->name("books.edit");
Route::post("/books/{book}/edit", "BooksController@save")->name("books.save");  

  
Route::get("/books/{book}", "BooksController@find")->name("book");
Route::get("/{locale}/books", "BooksController@list")->name("books");

Route::post("/books/{book}/reserve", "BooksController@reserve")->name("books.reserve");
Route::post("/books/{book}/unreserve", "BooksController@unreserve")->name("books.unreserve");
Route::post("/books/{book}/pass", "BooksController@pass")->name("books.pass");


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
