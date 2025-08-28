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
        Schema::table('users', function (Blueprint $table) {
            $table->json('solo_rank')->nullable()->after('puuid');
            $table->json('flex_rank')->nullable()->after('solo_rank');
            $table->string('summoner_icon')->nullable()->after('flex_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['solo_rank', 'flex_rank', 'summoner_icon']);
        });
    }
};
