<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\SalesTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Andara',
            'username' => 'admin',
            'email' => 'admin@andaramotor.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $this->token = $this->admin->createToken('admin-token')->plainTextToken;

        $m1 = Motor::create([
            'nama_motor' => 'Honda Beat',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2023,
            'harga_beli' => 12000000,
            'harga_jual' => 15000000,
            'kondisi' => 'Mulus',
            'jarak_tempuh' => 5000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'tersedia',
        ]);

        $m2 = Motor::create([
            'nama_motor' => 'Yamaha Vixion',
            'merk' => 'Yamaha',
            'tipe_motor' => 'Sport',
            'tahun' => 2021,
            'harga_beli' => 15000000,
            'harga_jual' => 18000000,
            'kondisi' => 'Bagus',
            'jarak_tempuh' => 20000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'terjual',
        ]);

        SalesTransaction::create([
            'kode_transaksi' => 'TRX-20261117-TEST',
            'motor_id' => $m2->id,
            'nama_pembeli' => 'Rina',
            'no_telepon_pembeli' => '08555666777',
            'tanggal_transaksi' => Carbon::now()->format('Y-m-d'),
            'harga_beli' => 15000000,
            'harga_jual' => 18000000,
            'keuntungan' => 3000000,
            'metode_pembayaran' => 'Cash',
        ]);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/admin/dashboard')->assertStatus(401);
    }

    public function test_authenticated_admin_gets_dashboard_statistics(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ringkasan_unit.total_motor', 2)
            ->assertJsonPath('data.ringkasan_unit.motor_tersedia', 1)
            ->assertJsonPath('data.ringkasan_unit.motor_terjual', 1)
            ->assertJsonPath('data.ringkasan_unit.motor_booking', 0)
            ->assertJsonPath('data.ringkasan_keuangan.total_omset', 18000000)
            ->assertJsonPath('data.ringkasan_keuangan.total_modal_terjual', 15000000)
            ->assertJsonPath('data.ringkasan_keuangan.total_keuntungan', 3000000)
            ->assertJsonPath('data.ringkasan_keuangan.total_aset_tersedia', 12000000)
            ->assertJsonPath('data.ringkasan_keuangan.estimasi_potensi_laba_tersedia', 3000000)
            ->assertJsonPath('data.performa_bulan_ini.unit_terjual', 1)
            ->assertJsonPath('data.performa_bulan_ini.omset', 18000000)
            ->assertJsonPath('data.performa_bulan_ini.keuntungan', 3000000)
            ->assertJsonCount(1, 'data.transaksi_terbaru')
            ->assertJsonCount(2, 'data.motor_terbaru');
    }
}
