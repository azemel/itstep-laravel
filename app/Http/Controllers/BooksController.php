<?php

namespace App\Http\Controllers;

use App\Author;
use App\Book;
use App\BookReservation;
use App\FavoriteBook;
use App\Http\Requests\BookRequest;
use App\Mail\BookIsReady;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BooksController extends Controller
{
    public function list($locale, Request $request) {

      App::setLocale($locale);
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
        ->withQuery($query)
        ;
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
      
      if (count($request->old())) {
        $book->title = $request->old("title");
        $book->isbn = $request->old("isbn");
        $book->year = $request->old("year");
      }
 
      return view("book-editor")->withBook($book);
    }
    
    public function add(Request $request) {

      Gate::authorize("create", Book::class);
      
      $validator = Validator::make($request->all(), [
        "title" => "required|min:3",
        "author" => "required", 
        "isbn" => "nullable|min:10|max:13",
        "year" => "nullable|integer|min:1500",
      ]);

      if (!$validator->fails()) {
        $book = new Book();

        $this->prepareBook($book, $validator->validated(), $request->file("cover"));
        
        return redirect(route("books.edit", ["book" => $book]));
      }


      $isbn = $request->input("isbn");
      if ($isbn) {
        $response = Http::withOptions(['verify' => false])
          ->get("https://openlibrary.org/api/books?bibkeys=ISBN:$isbn&jscmd=data&format=json");

        app("debugbar")->info($response);

        if ($response->ok()) {
          $json = $response->json();
          $json = $json["ISBN:$isbn"];
          app("debugbar")->info($json); 

          $data = [
            "title" => $json["title"],
            "year" => $json["publish_date"],
            "author" => $json["authors"][0]["name"],
            "isbn" => $isbn
          ];

          app("debugbar")->info($data);

          $book = new Book();
          $this->prepareBook($book, $data, false);

          $cover = $json["cover"]["medium"];

          //if cover exists
          $response = Http::withOptions(['verify' => false])
            ->get($cover);

          //if $response->ok

          
          app("debugbar")->info($response);
          $ext = substr($cover, strrpos($cover, ".") + 1);
          app("debugbar")->info($ext);

          $path = "public/covers/" . $book->id . "." . $ext;
          Storage::put($path, $response->body());

          $book->cover = $path;
          $book->save();
        }




        return redirect(route("books.edit", ["book" => $book]));

        // return view("dummy");
      }


      return redirect()
        ->back()
        ->withErrors($validator)
        ->withInput($request->all());
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
      $this->prepareBook($book, $request->validated(), $request->file("cover"));

      return redirect(route("books.edit", ["book" => $book]));
    }

    
    private function prepareBook(Book $book, array $data, $cover) {

      
      // $result = $request->validated();

      $author = Author::where("name", "=", $data["author"])->first();

      if (!$author) {
        $author = new Author();
        $author->name = $data["author"];
        $author->save();
      }

      $book->title = $data["title"];
      $book->isbn = $data["isbn"];
      $book->year = $data["year"];
      $book->author_id = $author->id;
      $book->user_id = Auth::id();

      $book->save();

      if ($cover){
        if (Storage::exists($book->cover)) {
          Storage::delete($book->cover);
        }
  
        $file = $cover;

        $book->cover = $file->storePubliclyAs("public/covers", $book->id . "." . $file->extension());    
      }
      
      $book->save();
    }

    public function reserve(Book $book) {
      
      if (!Gate::check("reserve", $book)) {
        return redirect()->route("book", ["book" => $book])->withMessage("Уже в очереди");
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
      return redirect()->route("book", ["book" => $book])->withMessage("Успех");
    }

    public function unreserve(Book $book) {

      Gate::authorize("unreserve", $book);
      
      BookReservation::where("book_id", "=", $book->id)
        ->where("user_id", "=", Auth::id())
        ->where("status", "=", 0)
        ->delete();

        
      // if next exists and curent user was first
      $nextReseravation = BookReservation::with("user")
        ->where("book_id", "=", $book->id)
        ->orderBy("created_at", "ASC")
        ->first();
    
      Mail::to($nextReseravation->user)->send(new BookIsReady($book));

      return redirect()->route("book", ["book" => $book])->withMessage("Успех");
    }  

    
    public function pass(Book $book) {

      Gate::authorize("pass", $book);
      
      $reservation = BookReservation::where("book_id", "=", $book->id)
        ->where("user_id", "=", Auth::id())
        ->where("status", "=", 0)
        ->first();

      $reservation->status = 1;
      $reservation->save();


      // if next exists
      $nextReseravation = BookReservation::with("user")
        ->where("book_id", "=", $book->id)
        ->orderBy("created_at", "ASC")
        ->first();
      

      Mail::to($nextReseravation->user)->send(new BookIsReady($book));

      // return view("dummy");
      return redirect()->route("book", ["book" => $book])->withMessage("Успех");
    }  

}
