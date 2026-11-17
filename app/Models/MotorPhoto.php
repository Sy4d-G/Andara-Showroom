<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotorPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'motor_id',
        'foto_path',
        'caption',
    ];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
