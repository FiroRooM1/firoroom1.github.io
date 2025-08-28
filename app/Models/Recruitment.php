<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'game_mode',
        'lane',
        'content',
    ];

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * パーティーとのリレーション
     */
    public function party()
    {
        return $this->hasOne(Party::class);
    }

    /**
     * 指定されたユーザーが既に申請済みかチェック
     */
    public function hasAppliedBy($userId)
    {
        return $this->applications()->where('applicant_id', $userId)->exists();
    }

    /**
     * 作成日時のフォーマット
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('Y/m/d');
    }
}
