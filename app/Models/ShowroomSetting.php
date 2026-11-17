<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowroomSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_showroom',
        'no_whatsapp',
        'alamat',
        'jam_operasional',
        'template_pesan_wa',
    ];
}
