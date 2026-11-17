<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShowroomSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowroomSettingController extends Controller
{
    /**
     * Get current showroom settings.
     */
    public function show(): JsonResponse
    {
        $setting = ShowroomSetting::firstOrCreate([], [
            'nama_showroom' => 'Andara Motor',
            'no_whatsapp' => '6281234567890',
            'alamat' => 'Jl. Andara Raya No. 88, Jakarta Selatan',
            'jam_operasional' => 'Senin - Sabtu 08:00 - 17:00 WIB',
            'template_pesan_wa' => 'Halo Andara Motor, saya tertarik dengan motor :nama (:tahun) seharga :harga. Apakah unit masih tersedia?',
        ]);

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Update showroom settings (Admin only).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_showroom' => ['sometimes', 'required', 'string', 'max:255'],
            'no_whatsapp' => ['sometimes', 'required', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'jam_operasional' => ['nullable', 'string', 'max:100'],
            'template_pesan_wa' => ['nullable', 'string'],
        ]);

        $setting = ShowroomSetting::firstOrCreate([]);
        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan showroom berhasil diperbarui.',
            'data' => $setting,
        ]);
    }
}
