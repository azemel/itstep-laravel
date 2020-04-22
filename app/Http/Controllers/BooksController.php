<?php

namespace App\Http\Controllers;

use App\Author;
use App\Book;
use App\BookReservation;
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

      return view("books")
        ->withBooks($result)
        ->withQuery($query);
    }

    
    public function find(Book $book, Request $request) {
      
      $book->reservations = $book->reservations()->with("user")->get();

      $orderNumber = null;
      if (Auth::id()) {
        $i = 0;
        foreach ($book->pending as $reservation) {
          if ($reservation->user_id === Auth::id()) {
            $orderNumber = $i;
            break;
          }
          $i++;
        }
      }

      return view("book")->withBook($book)->withOrderNumber($orderNumber);
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

    public function reserve(Book $book) {
      
      if (!Gate::check("reserve", $book)) {
        return redirect(route("book", ["book" => $book]))->withMessage("Уже в очереди");
      }

      $reservation = new BookReservation();
      $reservation->status = 0;
      $reservation->user_id = Auth::id();
      $reservation->book_id = $book->id;
      // $reservation->setRelation("user", Auth::user());
      // $reservation->setRelation("book", $book);

      // var_dump($reservation->user);
      $reservation->save();

      // return view("dummy");      
      return redirect(route("book", ["book" => $book]))->withMessage("Успех");
    }

    public function unreserve(Book $book) {

      Gate::authorize("unreserve", $book);
      
      BookReservation::where("book_id", "=", $book->id)
        ->where("user_id", "=", Auth::id())
        ->where("status", "=", 0)
        ->delete();

      return redirect(route("book", ["book" => $book]))->withMessage("Успех");
    }  

    
    public function pass(Book $book) {

      Gate::authorize("pass", $book);
      
      $reservation = BookReservation::where("book_id", "=", $book->id)
        ->where("user_id", "=", Auth::id())
        ->where("status", "=", 0)
        ->first();

      $reservation->status = 1;
      $reservation->save();

      return redirect(route("book", ["book" => $book]))->withMessage("Успех");
    }  

}
