<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Motor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_motor',
        'merk',
        'tipe_motor',
        'tahun',
        'harga_beli',
        'harga_jual',
        'kondisi',
        'jarak_tempuh',
        'surat_surat',
        'pajak',
        'garansi',
        'status',
        'foto_utama',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'harga_beli' => 'integer',
            'harga_jual' => 'integer',
            'jarak_tempuh' => 'integer',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MotorPhoto::class);
    }

    public function salesTransaction(): HasOne
    {
        return $this->hasOne(SalesTransaction::class);
    }
}
