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
        Schema::table('notifications', function (Blueprint $table) {
            // is_readカラムを削除
            $table->dropColumn('is_read');
            
            // read_atカラムを追加
            $table->timestamp('read_at')->nullable()->after('data');
            
            // インデックスを追加
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // read_atカラムを削除
            $table->dropColumn('read_at');
            
            // インデックスを削除
            $table->dropIndex(['user_id', 'read_at']);
            
            // is_readカラムを復元
            $table->boolean('is_read')->default(false)->after('data');
        });
    }
};
