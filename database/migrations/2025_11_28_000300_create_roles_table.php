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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('crew');
            $table->boolean('is_featured')->default(false);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('movie_people', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('person_id')
                ->constrained('roles')
                ->cascadeOnDelete();
        });

        $existingRoles = DB::table('movie_people')
            ->select('role')
            ->distinct()
            ->whereNotNull('role')
            ->pluck('role');

        $roleMap = [];

        foreach ($existingRoles as $code) {
            $roleMap[$code] = DB::table('roles')->insertGetId([
                'code' => $code,
                'name' => Str::headline($code),
                'category' => 'crew',
                'is_featured' => false,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($roleMap as $code => $id) {
            DB::table('movie_people')
                ->where('role', $code)
                ->update(['role_id' => $id]);
        }

        Schema::table('movie_people', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_people', function (Blueprint $table) {
            $table->string('role')->after('person_id');
        });

        $roleCodes = DB::table('roles')->pluck('code', 'id');

        $moviePeople = DB::table('movie_people')->select('id', 'role_id')->get();

        foreach ($moviePeople as $pivot) {
            DB::table('movie_people')
                ->where('id', $pivot->id)
                ->update(['role' => $roleCodes[$pivot->role_id] ?? null]);
        }

        Schema::table('movie_people', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
