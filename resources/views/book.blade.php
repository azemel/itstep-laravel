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
    
    @can('reserve', $book)
      <div>
        <form action="{{route("books.reserve", ["book" => $book])}}" method="post">
          @csrf
          <button type="submit">Забронировать</button>
        </form>
      </div>
    @endcan
      
    @can('unreserve', $book)
      <div>
        <form action="{{route("books.unreserv e", ["book" => $book])}}" method="post">
          @csrf
          <button type="submit">Отказаться</button>
        </form>
      </div>
    @endcan
    
    @can('pass', $book)
      <div>
        <form action="{{route("books.pass", ["book" => $book])}}" method="post">
          @csrf
          <button type="submit">Передать следующему</button>
        </form>
      </div>
    @endcan

    <div>
      Очередь: 
      @if ($orderNumber !== null) 
        {{$orderNumber + 1}} / 
      @endif
      {{$book->pending->count()}}
      <ol>
      
      @foreach($book->pending as $reservation)
        <li style="{{$reservation->user_id === Auth::id() ? "color: green" : ""}}">{{$reservation->user->email}}</li>
      @endforeach


      @if ($book->history->count() > 0)
        Эту книгу прочитали {{$book->history->count()}} раз
      @endif

      </ol>
      
    </div>


  </div>

</div>

@endsection 
