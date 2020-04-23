@php

$items  = [
  ["Main", route("index")],
  ["Books", route("books", ["locale" => "en"])],
];

if (Auth::id()) {
  $items[] = ["Add Book", route("books.new")];
} 

@endphp


<div class="navigation">

@foreach($items as $i => [$display, $uri])
  
  <a href="{{$uri}}" class="navigation__item{{$activeClass($i)}}">{{$display}}</a>

@endforeach
    <!-- Authentication Links -->
@guest
  <a class="navigation__item" href="{{ route('login') }}">{{ __('Login') }}</a>

  @if (Route::has('register'))
    <a class="navigation__item" href="{{ route('register') }}">{{ __('Register') }}</a>
  @endif

@else
  {{ Auth::user()->name }}
  
  <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
    {{ __('Logout') }}
  </a>
  
  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
  </form>
@endguest


</div>