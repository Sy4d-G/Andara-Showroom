<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_transaksi' => $this->kode_transaksi,
            'motor_id' => $this->motor_id,
            'nama_pembeli' => $this->nama_pembeli,
            'no_telepon_pembeli' => $this->no_telepon_pembeli,
            'alamat_pembeli' => $this->alamat_pembeli,
            'tanggal_transaksi' => $this->tanggal_transaksi?->format('Y-m-d'),
            'harga_beli' => $this->harga_beli,
            'harga_beli_formatted' => 'Rp ' . number_format($this->harga_beli, 0, ',', '.'),
            'harga_jual' => $this->harga_jual,
            'harga_jual_formatted' => 'Rp ' . number_format($this->harga_jual, 0, ',', '.'),
            'keuntungan' => $this->keuntungan,
            'keuntungan_formatted' => 'Rp ' . number_format($this->keuntungan, 0, ',', '.'),
            'metode_pembayaran' => $this->metode_pembayaran,
            'catatan' => $this->catatan,
            'motor' => $this->whenLoaded('motor', function () {
                return [
                    'id' => $this->motor->id,
                    'nama_motor' => $this->motor->nama_motor,
                    'merk' => $this->motor->merk,
                    'tipe_motor' => $this->motor->tipe_motor,
                    'tahun' => $this->motor->tahun,
                    'foto_utama' => $this->motor->foto_utama ? url('storage/' . $this->motor->foto_utama) : null,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
