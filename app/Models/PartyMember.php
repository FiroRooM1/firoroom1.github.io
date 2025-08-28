<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyMember extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'party_id',
        'user_id',
        'role',
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
