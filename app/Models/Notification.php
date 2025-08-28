<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 未読の通知を取得
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // 通知を既読にする
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}
