<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

  public function author()
  {
      return $this->belongsTo('App\Author');
  }

  public function getAuthorNameAttribute() {
    return $this->author ? $this->author->name : "";
  }
  
  public function owner()
  {
      return $this->belongsTo('App\User');
  }
  
}
