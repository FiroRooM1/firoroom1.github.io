<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'summoner_name',
        'summoner_level',
        'puuid',
        'solo_rank',
        'flex_rank',
        'summoner_icon',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'solo_rank' => 'array',
        'flex_rank' => 'array',
    ];

    /**
     * ソロランク情報を取得
     */
    public function getSoloRankAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * フレックスランク情報を取得
     */
    public function getFlexRankAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * ランク情報を設定
     */
    public function setRankInfo($soloRank = null, $flexRank = null)
    {
        $this->solo_rank = $soloRank;
        $this->flex_rank = $flexRank;
        $this->save();
    }

    /**
     * パーティーメンバーとのリレーション
     */
    public function partyMemberships()
    {
        return $this->hasMany(PartyMember::class);
    }

    /**
     * パーティーメッセージとのリレーション
     */
    public function partyMessages()
    {
        return $this->hasMany(PartyMessage::class);
    }

    /**
     * 通知とのリレーション
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * 未読通知数を取得
     */
    public function unreadNotificationsCount()
    {
        return $this->notifications()->unread()->count();
    }
}
