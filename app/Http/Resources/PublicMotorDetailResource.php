<?php

namespace App\Http\Resources;

use App\Models\ShowroomSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicMotorDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $setting = ShowroomSetting::first();
        $noWa = $setting?->no_whatsapp ?? '6281234567890';
        $template = $setting?->template_pesan_wa ?? 'Halo Andara Motor, saya tertarik dengan motor :nama (:tahun) seharga :harga. Apakah unit masih tersedia?';

        $hargaFormatted = 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
        $message = str_replace(
            [':nama', ':tahun', ':harga', ':id'],
            [$this->nama_motor, (string) $this->tahun, $hargaFormatted, (string) $this->id],
            $template
        );

        $cleanWa = preg_replace('/[^0-9]/', '', $noWa);
        $whatsappUrl = 'https://wa.me/' . $cleanWa . '?text=' . urlencode($message);

        return [
            'id' => $this->id,
            'nama_motor' => $this->nama_motor,
            'merk' => $this->merk,
            'tipe_motor' => $this->tipe_motor,
            'tahun' => $this->tahun,
            'harga_jual' => $this->harga_jual,
            'harga_jual_formatted' => $hargaFormatted,
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
            'whatsapp_url' => $whatsappUrl,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
