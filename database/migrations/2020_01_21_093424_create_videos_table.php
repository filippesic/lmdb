<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('video_type_id');
            $table->unsignedBigInteger('director_id');
            $table->string('poster');
            $table->string('trailer');
            $table->string('name');
            //$table->decimal('rating')->default(0); // TODO MtM
            $table->string('mpaa_rating');
            $table->unsignedInteger('duration_in_minutes');
            $table->date('release_date');
            $table->string('country');
            $table->text('plot');
            $table->timestamps();

            $table->unique(['name', 'country']);

            //$table->foreign('season_id')->references('id')->onDelete('cascade');
            $table->foreign('video_type_id')->references('id')->on('video_types')->onDelete('cascade');
            $table->foreign('director_id')->references('id')->on('artists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
