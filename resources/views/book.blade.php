@extends('layouts.base')

@section("title", "$book->title")

@section("body")

<div class="block book">

  <div class="book__cover-col">
    @if ($book->cover)
      <img class="book__cover" src="{{Storage::url($book->cover)}}"/>
    @else
      <div class="book__cover book__cover_placeholder">Нет обложки</div>
    @endif
  </div>

  <div class="book__info-col">
    <h3 class="book__title">{{$book->title}}</h3>
    <div class="book__author">{{$book->authorName}} • {{$book->year}}</div>
    <div class="book__isbn">ISBN {{$book->isbn}}</div>
  </div>

</div>

@endsection 
