<?php

namespace App\Http\Controllers;

use App\Book;
use App\BookReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    { 
      $user = Auth::user();

      $result = DB::select("
      WITH reservations
      AS (SELECT 
            *,
            ROW_NUMBER() OVER( PARTITION BY book_id ORDER BY created_at ASC) orderNumber
          FROM 
            book_reservations
      )
      SELECT 
        *
      FROM 
        reservations  
      WHERE reservations.user_id = ?
      ORDER BY reservations.created_at ASC  ",
      [$user->id]);

      // var_dump($result);

      $reservations = BookReservation::hydrate($result);  
      
      $ids = $reservations->map(fn($r) => $r->book_id)->all();


      $books = Book::whereIn("id", $ids)
        ->get()
        ->mapWithKeys(fn($b) => [$b->id =>  $b]);
      

      $reservations = $reservations->mapToGroups(function($r) {
        if ($r->status === 1) {        
          return ["history" => $r];
        }

        if ($r->orderNumber === 1) {
          return ["current" => $r];
        }

        return ["pending" => $r];
      });

      $reservations = collect(["pending" => [], "current" => [], "history" => []])->merge($reservations);
      
      app("debugbar")->info($reservations);
      // var_dump($books->toArray());
      
      // var_dump($reservations);


      // var_dump(BookReservation::with("book")
      // ->selectRaw("*, ROW_NUMBER() OVER( PARTITION BY book_id ORDER BY created_at DESC) orderNumber")
      // ->where("user_id", "=", $user->id)
      // ->get()->toArray());



      return view('home')->withReservations($reservations)->withBooks($books);
    }
}
