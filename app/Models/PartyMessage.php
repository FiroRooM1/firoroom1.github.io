<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_id',
        'user_id',
        'message',
    ];

    /**
     * パーティーとのリレーション
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
