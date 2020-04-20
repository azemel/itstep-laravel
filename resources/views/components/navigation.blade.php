@php

$items  = [
  ["Main", route("index")],
  ["Books", route("books")],
];

if (Auth::id()) {
  $items[] = ["Add Book", route("books.new")];
} 

@endphp


<div class="navigation">

@foreach($items as $i => [$display, $uri])
  
  <a href="{{$uri}}" class="navigation__item{{$activeClass($i)}}">{{$display}}</a>

@endforeach

</div>