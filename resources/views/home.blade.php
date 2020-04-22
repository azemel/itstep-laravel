@extends('layouts.base')

@section('body')

<h3>Ваши книги: </h3>
@foreach(Auth::user()->books as $book)
  {{$book->title}}
@endforeach


<h3>У вас на руках: </h3>
@foreach($reservations["current"] as $reservation)
  {{$books[$reservation->book_id]->title}}
@endforeach


<h3>В очереди: </h3>
@foreach($reservations["pending"] as $reservation)
  {{$books[$reservation->book_id]->title}} {{$reservation->orderNumber}}
@endforeach

<h3>Вы брали: </h3>
@foreach($reservations["history"] as $reservation)
  {{$books[$reservation->book_id]->title}}
@endforeach
@endsection
