<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->nullable()->unique();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('synopsis')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('year', 10)->nullable();
            $table->string('rating', 20)->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('image_url')->nullable();
            $table->string('backdrop_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->json('countries')->nullable();
            $table->json('spoken_languages')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
