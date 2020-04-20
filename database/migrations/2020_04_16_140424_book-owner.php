<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookOwner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("books", function(Blueprint $books) {
          $books->foreignId("user_id")->nullable();
          $books->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table("books", function(Blueprint $books) {
        $books->dropForeign("books_user_id_foreign");
        $books->dropColumn("user_id");
      });
    }
}
