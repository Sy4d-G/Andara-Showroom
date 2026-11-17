<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'motor_id',
        'nama_pembeli',
        'no_telepon_pembeli',
        'alamat_pembeli',
        'tanggal_transaksi',
        'harga_beli',
        'harga_jual',
        'keuntungan',
        'metode_pembayaran',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_transaksi' => 'date:Y-m-d',
            'harga_beli' => 'integer',
            'harga_jual' => 'integer',
            'keuntungan' => 'integer',
        ];
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
