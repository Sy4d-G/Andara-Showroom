<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTransactionApiTest extends TestCase
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
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_record_sales_transaction_calculates_profit_and_marks_motor_sold(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Honda PCX 160 ABS',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2023,
            'harga_beli' => 28000000,
            'harga_jual' => 32000000,
            'kondisi' => 'Istimewa',
            'jarak_tempuh' => 5000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'tersedia',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/admin/transaksi', [
                'motor_id' => $motor->id,
                'nama_pembeli' => 'Rian Hidayat',
                'no_telepon_pembeli' => '081298765432',
                'alamat_pembeli' => 'Jl. Galuh Mas, Karawang Barat',
                'tanggal_transaksi' => '2026-11-17',
                'harga_jual' => 32000000,
                'metode_pembayaran' => 'Transfer Bank',
                'catatan' => 'Bonus helm dan jaket',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.keuntungan', 4000000)
            ->assertJsonPath('data.harga_beli', 28000000)
            ->assertJsonPath('data.harga_jual', 32000000);

        // Verify motor status is now 'terjual'
        $this->assertEquals('terjual', $motor->fresh()->status);

        $this->assertDatabaseHas('sales_transactions', [
            'motor_id' => $motor->id,
            'nama_pembeli' => 'Rian Hidayat',
            'keuntungan' => 4000000,
        ]);
    }

    public function test_cannot_sell_already_sold_motor(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Yamaha Aerox 155',
            'merk' => 'Yamaha',
            'tipe_motor' => 'Matic',
            'tahun' => 2022,
            'harga_beli' => 20000000,
            'harga_jual' => 23000000,
            'kondisi' => 'Bagus',
            'jarak_tempuh' => 15000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'terjual',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/admin/transaksi', [
                'motor_id' => $motor->id,
                'nama_pembeli' => 'Ahmad',
                'no_telepon_pembeli' => '081233344455',
                'tanggal_transaksi' => '2026-11-17',
                'harga_jual' => 23000000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['motor_id']);
    }

    public function test_get_transaction_history_and_recap(): void
    {
        $motor1 = Motor::create([
            'nama_motor' => 'Honda Scoopy Prestige',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2022,
            'harga_beli' => 16000000,
            'harga_jual' => 19000000,
            'kondisi' => 'Mulus',
            'jarak_tempuh' => 9000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'terjual',
        ]);

        $motor2 = Motor::create([
            'nama_motor' => 'Kawasaki Ninja 250 FI',
            'merk' => 'Kawasaki',
            'tipe_motor' => 'Sport',
            'tahun' => 2020,
            'harga_beli' => 40000000,
            'harga_jual' => 46000000,
            'kondisi' => 'Koleksi',
            'jarak_tempuh' => 8000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'terjual',
        ]);

        SalesTransaction::create([
            'kode_transaksi' => 'TRX-20261101-001',
            'motor_id' => $motor1->id,
            'nama_pembeli' => 'Siti Aminah',
            'no_telepon_pembeli' => '08111222333',
            'tanggal_transaksi' => '2026-11-01',
            'harga_beli' => 16000000,
            'harga_jual' => 19000000,
            'keuntungan' => 3000000,
            'metode_pembayaran' => 'Cash',
        ]);

        SalesTransaction::create([
            'kode_transaksi' => 'TRX-20261110-002',
            'motor_id' => $motor2->id,
            'nama_pembeli' => 'Hendra Wijaya',
            'no_telepon_pembeli' => '08222333444',
            'tanggal_transaksi' => '2026-11-10',
            'harga_beli' => 40000000,
            'harga_jual' => 46000000,
            'keuntungan' => 6000000,
            'metode_pembayaran' => 'Transfer Bank',
        ]);

        // Test List
        $listResponse = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/transaksi');

        $listResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Test Recap
        $recapResponse = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/transaksi/rekap');

        $recapResponse->assertStatus(200)
            ->assertJsonPath('data.total_unit_terjual', 2)
            ->assertJsonPath('data.total_omset', 65000000)
            ->assertJsonPath('data.total_modal', 56000000)
            ->assertJsonPath('data.total_keuntungan', 9000000)
            ->assertJsonPath('data.rata_rata_keuntungan', 4500000)
            ->assertJsonCount(1, 'data.breakdown_bulanan');

        // Test filter by payment method
        $filteredList = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/transaksi?metode_pembayaran=Cash');

        $filteredList->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_pembeli', 'Siti Aminah');

        $filteredRecap = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/transaksi/rekap?metode_pembayaran=Cash');

        $filteredRecap->assertStatus(200)
            ->assertJsonPath('data.total_unit_terjual', 1)
            ->assertJsonPath('data.total_keuntungan', 3000000);
    }

    public function test_cannot_record_transaction_with_future_date(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Honda Beat Street',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2023,
            'harga_beli' => 14000000,
            'harga_jual' => 17000000,
            'kondisi' => 'Mulus',
            'jarak_tempuh' => 6000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'tersedia',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/admin/transaksi', [
                'motor_id' => $motor->id,
                'nama_pembeli' => 'Budi Santoso',
                'no_telepon_pembeli' => '08123456789',
                'tanggal_transaksi' => now()->addDay()->format('Y-m-d'),
                'harga_jual' => 17000000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tanggal_transaksi']);
    }

    public function test_admin_transaction_and_report_ui_routes_render(): void
    {
        $this->get('/admin/transaksi')->assertOk();
        $this->get('/admin/transaksi/create')->assertOk();
        $this->get('/admin/transaksi/1')->assertOk();
        $this->get('/admin/laporan')->assertOk();
    }
}