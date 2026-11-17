<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelExportApiTest extends TestCase
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

        $motor = Motor::create([
            'nama_motor' => 'Yamaha NMAX 155',
            'merk' => 'Yamaha',
            'tipe_motor' => 'Matic',
            'tahun' => 2022,
            'harga_beli' => 22000000,
            'harga_jual' => 25500000,
            'kondisi' => 'Mulus',
            'jarak_tempuh' => 12000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'terjual',
        ]);

        SalesTransaction::create([
            'kode_transaksi' => 'TRX-20261117-001',
            'motor_id' => $motor->id,
            'nama_pembeli' => 'Dedi Kurniawan',
            'no_telepon_pembeli' => '081234567890',
            'tanggal_transaksi' => '2026-11-17',
            'harga_beli' => 22000000,
            'harga_jual' => 25500000,
            'keuntungan' => 3500000,
            'metode_pembayaran' => 'Cash',
        ]);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
        ];
    }

    public function test_export_requires_authentication(): void
    {
        $this->getJson('/api/admin/transaksi/export/excel')->assertStatus(401);
        $this->getJson('/api/admin/transaksi/export/csv')->assertStatus(401);
    }

    public function test_export_excel_returns_xlsx_file_download(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->get('/api/admin/transaksi/export/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('laporan_transaksi_', $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->streamedContent());
    }

    public function test_export_csv_returns_csv_file_download(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->get('/api/admin/transaksi/export/csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->streamedContent());
        $content = $response->streamedContent();
        $this->assertStringContainsString('TRX-20261117-001', $content);
        $this->assertStringContainsString('Dedi Kurniawan', $content);
        $this->assertStringContainsString('Yamaha NMAX 155', $content);
    }
}
