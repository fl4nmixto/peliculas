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
        Schema::table('movies', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('title');
            $table->text('synopsis')->nullable()->after('tagline');
            $table->unsignedTinyInteger('score')->default(0)->after('rating');
            $table->string('trailer_url')->nullable()->after('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'synopsis', 'score', 'trailer_url']);
        });
    }
};
