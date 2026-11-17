<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMotorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $potensiKeuntungan = $this->harga_jual - $this->harga_beli;

        return [
            'id' => $this->id,
            'nama_motor' => $this->nama_motor,
            'merk' => $this->merk,
            'tipe_motor' => $this->tipe_motor,
            'tahun' => $this->tahun,
            'harga_beli' => $this->harga_beli,
            'harga_beli_formatted' => 'Rp ' . number_format($this->harga_beli, 0, ',', '.'),
            'harga_jual' => $this->harga_jual,
            'harga_jual_formatted' => 'Rp ' . number_format($this->harga_jual, 0, ',', '.'),
            'potensi_keuntungan' => $potensiKeuntungan,
            'potensi_keuntungan_formatted' => 'Rp ' . number_format($potensiKeuntungan, 0, ',', '.'),
            'kondisi' => $this->kondisi,
            'jarak_tempuh' => $this->jarak_tempuh,
            'surat_surat' => $this->surat_surat,
            'pajak' => $this->pajak,
            'garansi' => $this->garansi,
            'status' => $this->status,
            'deskripsi' => $this->deskripsi,
            'foto_utama' => $this->foto_utama ? url('storage/' . $this->foto_utama) : null,
            'photos' => $this->photos->map(fn ($p) => [
                'id' => $p->id,
                'foto_url' => url('storage/' . $p->foto_path),
                'caption' => $p->caption,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
