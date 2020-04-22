<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("authors", function(Blueprint $authors) {
          $authors->id();
          $authors->timestamps();
          $authors->string("name", 200);
          $authors->date("dateOfBirth")->nullable();
        });

        Schema::create('books', function (Blueprint $books) {
          $books->id();
          $books->timestamps();
          $books->string("title", 200);
          $books->foreignId("author_id");
          $books->char("isbn", 13)->nullable();
          $books->integer("year")->nullable();
          $books->boolean("isAvailable")->default(false);

          $books->foreign('author_id')->references('id')->on('authors');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
        Schema::dropIfExists('authors');
    }
}
