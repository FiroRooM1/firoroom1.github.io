<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('summoner_level')->nullable()->after('summoner_name');
            $table->string('puuid')->nullable()->after('summoner_level');
            $table->string('account_id')->nullable()->after('puuid');
            $table->string('summoner_id')->nullable()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['summoner_level', 'puuid', 'account_id', 'summoner_id']);
        });
    }
};
