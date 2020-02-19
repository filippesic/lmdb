<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('artist_type_id');
            $table->string('poster');
            $table->string('name');
            $table->string('surname');
            $table->text('bio');
            $table->string('gender');
            $table->date('birth_date');
            $table->string('country');
            $table->timestamps();

            $table->foreign('artist_type_id')->references('id')->on('artist_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artists');
    }
}
