@php
  $isNew = !$book->id;
@endphp

@extends('layouts.base', [
  "navigationItem" => ($isNew ? 2 : null)
])

@section("title", $isNew ? "Новая книга" : "Редактирование: $book->title")

@section("body")
<form class="block form" method="post" enctype="multipart/form-data">
  @csrf
  <x-form-field key="title" name="Название книги" :value="$book->title"/>

  <x-form-field key="author" name="Автор" :value="($book->author_name)"/>
  <ul id="authors-hints"></ul>
  
  <x-form-field key="year" name="Год издания" :value="$book->year"/>
  <x-form-field key="isbn" name="ISBN" :value="$book->isbn"/>

  <img src="{{Storage::url($book->cover)}}" width="25"/>
  <x-form-field key="cover" name="Обложка" :value="$book->cover" type="file"/>

  <div class="form__field"> 
    <button class="button" type="submit">Сохранить</button>
  </div>
</form>

<script>
  window.addEventListener("load", () => {
    const authorField = document.getElementsByName("author")[0];
    const hints = document.getElementById("authors-hints");

    authorField.addEventListener("input", (e) => {
      const value = authorField.value;

      hints.innerHTML = "... загрузка";
      if (value.length >= 2) {
        fetch("{{route("api.authors.hints")}}?q=" + value)
          .then((response) => {
            console.log(response);
            return response.json();
          })
          .then((data) => {
            hints.innerHTML = "";
            hints.append(...data.map(name => {
              const li = document.createElement("li");
              li.innerText = name;
              return li;
            }));
          });
      }

    });

  });
</script>

@endsection

