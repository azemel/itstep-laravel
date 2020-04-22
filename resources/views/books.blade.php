BOOKS
@extends('layouts.base', ["navigationItem" => 1])

@section('title', "Books")

@section("body")

<div class="books block">

  <form method="get">
    <input name="q" value="{{$query}}"/>
    <button type="submit">Поиск</button>
  </form>

  @forelse($books as $book)
    <div class="books__book">
      <a href="{{route("book", ["book" => $book])}}">{{$book->title}}</a> 
      by 
      {{$book->authorName}} 
      
      @can('update', $book)
        <a href="{{route("books.edit", ["book" => $book])}}">Edit</a>  
      @endcan 
    </div>

  @empty
    <div>Ничего не найдено</div>
  @endforelse
  

</div>

@endsection