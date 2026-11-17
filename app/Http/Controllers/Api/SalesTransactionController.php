<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesTransactionRequest;
use App\Http\Resources\SalesTransactionResource;
use App\Models\Motor;
use App\Models\SalesTransaction;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesTransactionController extends Controller
{
    /**
     * Display a listing of sales transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SalesTransaction::with('motor');

        // Date range filter
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->input('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->input('tanggal_selesai'));
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('nama_pembeli', 'like', "%{$search}%")
                    ->orWhere('no_telepon_pembeli', 'like', "%{$search}%")
                    ->orWhereHas('motor', function ($mq) use ($search) {
                        $mq->where('nama_motor', 'like', "%{$search}%")
                            ->orWhere('merk', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $transactions = $query->orderBy('tanggal_transaksi', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SalesTransactionResource::collection($transactions->items()),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Store a newly recorded sales transaction.
     */
    public function store(StoreSalesTransactionRequest $request): JsonResponse
    {
        $transaction = DB::transaction(function () use ($request) {
            $motor = Motor::lockForUpdate()->findOrFail($request->validated('motor_id'));

            $hargaBeli = $request->input('harga_beli') !== null
                ? (int) $request->input('harga_beli')
                : (int) $motor->harga_beli;

            $hargaJual = (int) $request->validated('harga_jual');
            $keuntungan = $hargaJual - $hargaBeli;

            // Generate unique transaction code: TRX-YYYYMMDD-RANDOM4
            $datePrefix = Carbon::parse($request->validated('tanggal_transaksi'))->format('Ymd');
            $randomSuffix = strtoupper(bin2hex(random_bytes(2)));
            $kodeTransaksi = 'TRX-'.$datePrefix.'-'.$randomSuffix;

            $transaction = SalesTransaction::create([
                'kode_transaksi' => $kodeTransaksi,
                'motor_id' => $motor->id,
                'nama_pembeli' => $request->validated('nama_pembeli'),
                'no_telepon_pembeli' => $request->validated('no_telepon_pembeli'),
                'alamat_pembeli' => $request->validated('alamat_pembeli'),
                'tanggal_transaksi' => $request->validated('tanggal_transaksi'),
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'keuntungan' => $keuntungan,
                'metode_pembayaran' => $request->input('metode_pembayaran', 'Cash'),
                'catatan' => $request->input('catatan'),
            ]);

            // Update motor status to 'terjual'
            $motor->status = 'terjual';
            $motor->save();

            return $transaction->load('motor');
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi penjualan berhasil dicatat dan status motor diubah menjadi terjual.',
            'data' => new SalesTransactionResource($transaction),
        ], 201);
    }

    /**
     * Display the specified transaction detail.
     */
    public function show(int $id): JsonResponse
    {
        $transaction = SalesTransaction::with('motor')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SalesTransactionResource($transaction),
        ]);
    }

    /**
     * Get transaction summary and financial recap.
     */
    public function rekap(Request $request): JsonResponse
    {
        $query = SalesTransaction::query();

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->input('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->input('tanggal_selesai'));
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_transaksi', (int) $request->input('tahun'));
        }

        $totalUnit = (int) $query->count();
        $totalOmset = (int) $query->sum('harga_jual');
        $totalModal = (int) $query->sum('harga_beli');
        $totalKeuntungan = (int) $query->sum('keuntungan');

        // Monthly breakdown
        $monthlyQuery = SalesTransaction::query();
        if ($request->filled('tahun')) {
            $monthlyQuery->whereYear('tanggal_transaksi', (int) $request->input('tahun'));
        } else {
            $monthlyQuery->whereYear('tanggal_transaksi', date('Y'));
        }

        $monthlyStats = $monthlyQuery->select([
            DB::raw("strftime('%m', tanggal_transaksi) as bulan"),
            DB::raw('COUNT(*) as total_unit'),
            DB::raw('SUM(harga_jual) as total_omset'),
            DB::raw('SUM(harga_beli) as total_modal'),
            DB::raw('SUM(keuntungan) as total_keuntungan'),
        ])
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'bulan' => (int) $item->bulan,
                    'nama_bulan' => Carbon::createFromDate(null, (int) $item->bulan, 1)->translatedFormat('F'),
                    'total_unit' => (int) $item->total_unit,
                    'total_omset' => (int) $item->total_omset,
                    'total_modal' => (int) $item->total_modal,
                    'total_keuntungan' => (int) $item->total_keuntungan,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_unit_terjual' => $totalUnit,
                'total_omset' => $totalOmset,
                'total_omset_formatted' => 'Rp '.number_format($totalOmset, 0, ',', '.'),
                'total_modal' => $totalModal,
                'total_modal_formatted' => 'Rp '.number_format($totalModal, 0, ',', '.'),
                'total_keuntungan' => $totalKeuntungan,
                'total_keuntungan_formatted' => 'Rp '.number_format($totalKeuntungan, 0, ',', '.'),
                'breakdown_bulanan' => $monthlyStats,
            ],
        ]);
    }

    /**
     * Export sales transaction report to Excel (.xlsx).
     */
    public function exportExcel(Request $request, ExcelExportService $exportService): StreamedResponse
    {
        $query = SalesTransaction::query();

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->input('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->input('tanggal_selesai'));
        }

        return $exportService->exportExcel($query);
    }

    /**
     * Export sales transaction report to CSV (.csv).
     */
    public function exportCsv(Request $request, ExcelExportService $exportService): StreamedResponse
    {
        $query = SalesTransaction::query();

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->input('tanggal_mulai'));
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->input('tanggal_selesai'));
        }

        return $exportService->exportCsv($query);
    }
}
