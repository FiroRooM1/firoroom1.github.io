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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 通知を受け取るユーザー
            $table->string('type'); // 通知の種類（application_received, application_approved, application_rejected）
            $table->text('message'); // 通知メッセージ
            $table->json('data')->nullable(); // 関連データ（申請ID、募集タイトルなど）
            $table->timestamp('read_at')->nullable(); // 既読日時
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
