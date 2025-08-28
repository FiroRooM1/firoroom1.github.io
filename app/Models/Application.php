<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_id',
        'applicant_id',
        'preferred_lane',
        'message',
        'status',
    ];

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
