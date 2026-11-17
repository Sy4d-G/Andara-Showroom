<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicMotorResource extends JsonResource
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
            'nama_motor' => $this->nama_motor,
            'merk' => $this->merk,
            'tipe_motor' => $this->tipe_motor,
            'tahun' => $this->tahun,
            'harga_jual' => $this->harga_jual,
            'kondisi' => $this->kondisi,
            'jarak_tempuh' => $this->jarak_tempuh,
            'surat_surat' => $this->surat_surat,
            'pajak' => $this->pajak,
            'garansi' => $this->garansi,
            'status' => $this->status,
            'foto_utama' => $this->foto_utama ? url('storage/'.$this->foto_utama) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
