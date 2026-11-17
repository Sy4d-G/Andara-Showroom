<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Motor;
use App\Models\SalesTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary and analytics for admin.
     */
    public function index(): JsonResponse
    {
        // 1. Motor Unit Summary
        $totalMotor = Motor::count();
        $motorTersedia = Motor::where('status', 'tersedia')->count();
        $motorTerjual = Motor::where('status', 'terjual')->count();
        $motorBooking = Motor::where('status', 'booking')->count();

        // 2. Financial Summary
        $totalOmset = (int) SalesTransaction::sum('harga_jual');
        $totalModalTerjual = (int) SalesTransaction::sum('harga_beli');
        $totalKeuntungan = (int) SalesTransaction::sum('keuntungan');

        $totalAsetTersedia = (int) Motor::where('status', 'tersedia')->sum('harga_beli');
        $estimasiLabaTersedia = (int) Motor::where('status', 'tersedia')
            ->selectRaw('SUM(harga_jual - harga_beli) as laba')
            ->value('laba') ?? 0;

        // 3. This Month Performance
        $now = Carbon::now();
        $currentMonthQuery = SalesTransaction::query()
            ->whereYear('tanggal_transaksi', $now->year)
            ->whereMonth('tanggal_transaksi', $now->month);

        $unitBulanIni = (int) (clone $currentMonthQuery)->count();
        $omsetBulanIni = (int) (clone $currentMonthQuery)->sum('harga_jual');
        $labaBulanIni = (int) (clone $currentMonthQuery)->sum('keuntungan');

        // 4. Recent Transactions (5)
        $transaksiTerbaru = SalesTransaction::with('motor')
            ->orderBy('tanggal_transaksi', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'kode_transaksi' => $t->kode_transaksi,
                    'tanggal' => $t->tanggal_transaksi?->format('Y-m-d'),
                    'motor' => $t->motor?->nama_motor ?? '-',
                    'nama_pembeli' => $t->nama_pembeli,
                    'harga_jual' => $t->harga_jual,
                    'harga_jual_formatted' => 'Rp ' . number_format($t->harga_jual, 0, ',', '.'),
                    'keuntungan' => $t->keuntungan,
                    'keuntungan_formatted' => 'Rp ' . number_format($t->keuntungan, 0, ',', '.'),
                ];
            });

        // 5. Recent Motor Arrivals (5)
        $motorTerbaru = Motor::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nama_motor' => $m->nama_motor,
                    'merk' => $m->merk,
                    'tipe_motor' => $m->tipe_motor,
                    'tahun' => $m->tahun,
                    'harga_beli' => $m->harga_beli,
                    'harga_jual' => $m->harga_jual,
                    'status' => $m->status,
                    'foto_utama' => $m->foto_utama ? url('storage/' . $m->foto_utama) : null,
                ];
            });

        // 6. Sales Trend (Last 6 Months)
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;

            $monthData = SalesTransaction::query()
                ->whereYear('tanggal_transaksi', $year)
                ->whereMonth('tanggal_transaksi', $month);

            $trend[] = [
                'periode' => $monthDate->format('Y-m'),
                'label' => $monthDate->translatedFormat('M Y'),
                'unit' => (int) (clone $monthData)->count(),
                'omset' => (int) (clone $monthData)->sum('harga_jual'),
                'keuntungan' => (int) (clone $monthData)->sum('keuntungan'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ringkasan_unit' => [
                    'total_motor' => $totalMotor,
                    'motor_tersedia' => $motorTersedia,
                    'motor_terjual' => $motorTerjual,
                    'motor_booking' => $motorBooking,
                ],
                'ringkasan_keuangan' => [
                    'total_omset' => $totalOmset,
                    'total_omset_formatted' => 'Rp ' . number_format($totalOmset, 0, ',', '.'),
                    'total_modal_terjual' => $totalModalTerjual,
                    'total_modal_terjual_formatted' => 'Rp ' . number_format($totalModalTerjual, 0, ',', '.'),
                    'total_keuntungan' => $totalKeuntungan,
                    'total_keuntungan_formatted' => 'Rp ' . number_format($totalKeuntungan, 0, ',', '.'),
                    'total_aset_tersedia' => $totalAsetTersedia,
                    'total_aset_tersedia_formatted' => 'Rp ' . number_format($totalAsetTersedia, 0, ',', '.'),
                    'estimasi_potensi_laba_tersedia' => $estimasiLabaTersedia,
                    'estimasi_potensi_laba_tersedia_formatted' => 'Rp ' . number_format($estimasiLabaTersedia, 0, ',', '.'),
                ],
                'performa_bulan_ini' => [
                    'bulan' => $now->translatedFormat('F Y'),
                    'unit_terjual' => $unitBulanIni,
                    'omset' => $omsetBulanIni,
                    'omset_formatted' => 'Rp ' . number_format($omsetBulanIni, 0, ',', '.'),
                    'keuntungan' => $labaBulanIni,
                    'keuntungan_formatted' => 'Rp ' . number_format($labaBulanIni, 0, ',', '.'),
                ],
                'transaksi_terbaru' => $transaksiTerbaru,
                'motor_terbaru' => $motorTerbaru,
                'tren_penjualan' => $trend,
            ],
        ]);
    }
}
