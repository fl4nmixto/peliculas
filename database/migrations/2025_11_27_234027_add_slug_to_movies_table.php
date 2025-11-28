<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('slug')->unique()->after('title');
        });

        $movies = DB::table('movies')->select('id', 'title')->get();

        foreach ($movies as $movie) {
            $slug = Str::slug($movie->title);

            if (empty($slug)) {
                $slug = 'movie-' . $movie->id;
            }

            DB::table('movies')->where('id', $movie->id)->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
