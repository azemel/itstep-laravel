<?php

namespace App\Http\Controllers;

use App\Author;
use Illuminate\Http\Request;

class AuthorsController extends Controller
{
  public function hints(Request $request) {
    
    $query = $request->input("q");
    
    $list = Author::select("name")
      ->where("name", "LIKE", "$query%")
      ->limit(5)
      ->get()
      ->toArray();

    $list = array_map(fn($a) => $a["name"], $list);

    return $list;
  }
}
