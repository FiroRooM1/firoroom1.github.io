<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_id',
        'name',
        'description',
        'status',
    ];

    /**
     * 募集とのリレーション
     */
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    /**
     * パーティーメンバーとのリレーション
     */
    public function members()
    {
        return $this->hasMany(PartyMember::class);
    }

    /**
     * パーティーメッセージとのリレーション
     */
    public function messages()
    {
        return $this->hasMany(PartyMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * 指定されたユーザーがパーティーメンバーかチェック
     */
    public function hasMember($userId)
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * 指定されたユーザーがパーティーリーダーかチェック
     */
    public function isLeader($userId)
    {
        return $this->members()->where('user_id', $userId)->where('role', 'leader')->exists();
    }
}
