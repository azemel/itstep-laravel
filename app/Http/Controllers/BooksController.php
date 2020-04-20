<?php

namespace App\Http\Controllers;

use App\Author;
use App\Book;
use App\FavoriteBook;
use App\Http\Requests\BookRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BooksController extends Controller
{
    public function list(Request $request) {

      $query = $request->input("q");

      // title @by author 

      if ($query) {

        $match = []; 
        preg_match("/^(?<title>.*?)\s*(@by\s+(?<author>.*?)\s*)?$/", $query, $match);
        
        $title = @$match["title"];
        $author = @$match["author"];

        var_dump($title);
        $result = Book::with("author");
        
        if ($title) {
          $result = $result->where("title", "LIKE", "%$title%");
        }

        if ($author) {
          $result = $result->whereHas("author", fn($query) => $query->where("name", "LIKE", "%$author%"));
          // $result = $result->where("author.name",  "LIKE", "%$author%");
        }

        $result = $result->get();
      } else {
        $result = Book::with("author")->get();
      }

      return view("books")->withBooks($result)->withQuery($query);
    }

    
    public function find(Book $book, Request $request) {
      return view("book")->withBook($book);
    }

    
    public function create(Request $request) {

      Gate::authorize("create", Book::class);

      $book = new Book();
      return view("book-editor")->withBook($book);
    }
    
    public function add(BookRequest $request) {

      Gate::authorize("create", Book::class);

      $book = new Book();
      
      $this->prepareBook($book, $request);

      
      return redirect(route("books.edit", ["book" => $book]));
    }

    public function edit(Book $book, Request $request) {

      Gate::authorize("update", $book);

      if (count($request->old())) {
        $book->title = $request->old("title");
        $book->isbn = $request->old("isbn");
        $book->year = $request->old("year");
      }

      return view("book-editor")->withBook($book);
    }


    public function save(Book $book, BookRequest $request) {

      Gate::authorize("update", $book);
      $this->prepareBook($book, $request);

      return redirect(route("books.edit", ["book" => $book]));
    }

    
    private function prepareBook(Book $book, BookRequest $request) {

      $result = $request->validated();

      $author = Author::where("name", "=", $result["author"])->first();

      if (!$author) {
        $author = new Author();
        $author->name = $result["author"];
        $author->save();
      }

      $book->title = $result["title"];
      $book->isbn = $result["isbn"];
      $book->year = $result["year"];
      $book->author_id = $author->id;
      $book->user_id = Auth::id();

      $book->save();

      if ($request->file("cover")){
        if (Storage::exists($book->cover)) {
          Storage::delete($book->cover);
        }
  
        $file = $request->file("cover");

        $book->cover = $file->storePubliclyAs("public/covers", $book->id . "." . $file->extension());    
      }
      
      $book->save();
    }

}
