<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicMotorDetailResource;
use App\Http\Resources\PublicMotorResource;
use App\Models\Motor;
use App\Models\ShowroomSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMotorController extends Controller
{
    /**
     * Get list of motors for public catalog.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Motor::query();

        // Default to 'tersedia' unless explicit filter is provided
        if ($request->filled('status')) {
            if ($request->input('status') !== 'all') {
                $query->where('status', $request->input('status'));
            }
        } else {
            $query->where('status', 'tersedia');
        }

        // Search keyword
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_motor', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('tipe_motor', 'like', "%{$search}%")
                    ->orWhere('kondisi', 'like', "%{$search}%");
            });
        }

        // Filter by Merk
        if ($request->filled('merk')) {
            $query->where('merk', $request->input('merk'));
        }

        // Filter by Tipe Motor
        if ($request->filled('tipe_motor')) {
            $query->where('tipe_motor', $request->input('tipe_motor'));
        }

        // Filter by Year range
        if ($request->filled('tahun_min')) {
            $query->where('tahun', '>=', (int) $request->input('tahun_min'));
        }
        if ($request->filled('tahun_max')) {
            $query->where('tahun', '<=', (int) $request->input('tahun_max'));
        }

        // Filter by Price range
        if ($request->filled('harga_min')) {
            $query->where('harga_jual', '>=', (int) $request->input('harga_min'));
        }
        if ($request->filled('harga_max')) {
            $query->where('harga_jual', '<=', (int) $request->input('harga_max'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSorts = ['harga_jual', 'tahun', 'jarak_tempuh', 'created_at', 'nama_motor'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->input('per_page', 12), 50);
        $motors = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PublicMotorResource::collection($motors->items()),
            'meta' => [
                'current_page' => $motors->currentPage(),
                'last_page' => $motors->lastPage(),
                'per_page' => $motors->perPage(),
                'total' => $motors->total(),
            ],
        ]);
    }

    /**
     * Get detail of a specific motor.
     */
    public function show(int $id): JsonResponse
    {
        $motor = Motor::with('photos')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new PublicMotorDetailResource($motor),
        ]);
    }

    /**
     * Get metadata for catalog filter controls.
     */
    public function filterMeta(): JsonResponse
    {
        $merks = Motor::query()->distinct()->pluck('merk')->sort()->values();
        $tipeMotors = Motor::query()->distinct()->pluck('tipe_motor')->sort()->values();

        $tahunMin = (int) (Motor::query()->min('tahun') ?? 2015);
        $tahunMax = (int) (Motor::query()->max('tahun') ?? date('Y'));
        $hargaMin = (int) (Motor::query()->min('harga_jual') ?? 0);
        $hargaMax = (int) (Motor::query()->max('harga_jual') ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                'merks' => $merks,
                'tipe_motors' => $tipeMotors,
                'tahun_min' => $tahunMin,
                'tahun_max' => $tahunMax,
                'harga_min' => $hargaMin,
                'harga_max' => $hargaMax,
            ],
        ]);
    }

    /**
     * Get showroom contact and profile information.
     */
    public function kontak(): JsonResponse
    {
        $setting = ShowroomSetting::first();

        return response()->json([
            'success' => true,
            'data' => [
                'nama_showroom' => $setting?->nama_showroom ?? 'Andara Motor',
                'no_whatsapp' => $setting?->no_whatsapp ?? '6281234567890',
                'alamat' => $setting?->alamat ?? 'Jl. Andara Raya No. 88, Jakarta Selatan',
                'jam_operasional' => $setting?->jam_operasional ?? 'Senin - Sabtu 08:00 - 17:00 WIB',
                'template_pesan_wa' => $setting?->template_pesan_wa,
            ],
        ]);
    }
}
