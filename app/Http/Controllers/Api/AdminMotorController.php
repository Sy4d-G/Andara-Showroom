<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMotorRequest;
use App\Http\Requests\UpdateMotorRequest;
use App\Http\Resources\AdminMotorResource;
use App\Models\Motor;
use App\Models\MotorPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminMotorController extends Controller
{
    /**
     * List all motors for admin management.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Motor::with('photos');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search keyword
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_motor', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('tipe_motor', 'like', "%{$search}%");
            });
        }

        // Merk filter
        if ($request->filled('merk')) {
            $query->where('merk', $request->input('merk'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSorts = ['harga_beli', 'harga_jual', 'tahun', 'created_at', 'status', 'nama_motor'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->input('per_page', 15), 100);
        $motors = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AdminMotorResource::collection($motors->items()),
            'meta' => [
                'current_page' => $motors->currentPage(),
                'last_page' => $motors->lastPage(),
                'per_page' => $motors->perPage(),
                'total' => $motors->total(),
            ],
        ]);
    }

    /**
     * Store a newly created motor in storage.
     */
    public function store(StoreMotorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $motor = DB::transaction(function () use ($request, $data) {
            if ($request->hasFile('foto_utama')) {
                $data['foto_utama'] = $request->file('foto_utama')->store('motors', 'public');
            }

            $motor = Motor::create($data);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photoFile) {
                    $photoPath = $photoFile->store('motors/photos', 'public');
                    MotorPhoto::create([
                        'motor_id' => $motor->id,
                        'foto_path' => $photoPath,
                        'caption' => 'Foto ' . ($index + 1),
                    ]);
                }
            }

            return $motor->load('photos');
        });

        return response()->json([
            'success' => true,
            'message' => 'Data motor berhasil ditambahkan.',
            'data' => new AdminMotorResource($motor),
        ], 201);
    }

    /**
     * Display the specified motor.
     */
    public function show(int $id): JsonResponse
    {
        $motor = Motor::with(['photos', 'salesTransaction'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AdminMotorResource($motor),
        ]);
    }

    /**
     * Update the specified motor in storage.
     */
    public function update(UpdateMotorRequest $request, int $id): JsonResponse
    {
        $motor = Motor::with('photos')->findOrFail($id);
        $data = $request->validated();

        $updatedMotor = DB::transaction(function () use ($request, $motor, $data) {
            if ($request->hasFile('foto_utama')) {
                if ($motor->foto_utama && Storage::disk('public')->exists($motor->foto_utama)) {
                    Storage::disk('public')->delete($motor->foto_utama);
                }
                $data['foto_utama'] = $request->file('foto_utama')->store('motors', 'public');
            }

            $motor->update($data);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photoFile) {
                    $photoPath = $photoFile->store('motors/photos', 'public');
                    MotorPhoto::create([
                        'motor_id' => $motor->id,
                        'foto_path' => $photoPath,
                        'caption' => null,
                    ]);
                }
            }

            return $motor->fresh(['photos', 'salesTransaction']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data motor berhasil diperbarui.',
            'data' => new AdminMotorResource($updatedMotor),
        ]);
    }

    /**
     * Remove the specified motor from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $motor = Motor::with('photos')->findOrFail($id);

        DB::transaction(function () use ($motor) {
            if ($motor->foto_utama && Storage::disk('public')->exists($motor->foto_utama)) {
                Storage::disk('public')->delete($motor->foto_utama);
            }

            foreach ($motor->photos as $photo) {
                if (Storage::disk('public')->exists($photo->foto_path)) {
                    Storage::disk('public')->delete($photo->foto_path);
                }
                $photo->delete();
            }

            $motor->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Data motor berhasil dihapus.',
        ]);
    }
}
