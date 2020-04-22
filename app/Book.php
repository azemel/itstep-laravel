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

  public function reservations()
  {
    return $this->hasMany('App\BookReservation')->orderBy("created_at", "ASC")  ;
  }

  public function getPendingAttribute() { 
    return $this->reservations->filter(fn($r) => $r->status === 0);//()->where("status", "=", 0)->get();
  }
  
  public function getHistoryAttribute() { 
    return $this->reservations->filter(fn($r) => $r->status === 1);
  }

}
