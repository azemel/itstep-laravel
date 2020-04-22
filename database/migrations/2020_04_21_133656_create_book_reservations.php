<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBookReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('book_reservations', function (Blueprint $reservation) {
            $reservation->id();
            $reservation->timestamps();

            $reservation->integer("status");

            $reservation->foreignId("user_id");
            $reservation->foreignId("book_id");

            $reservation->foreign('user_id')->references('id')->on('users');
            $reservation->foreign('book_id')->references('id')->on('books');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_reservations');
    }
}
