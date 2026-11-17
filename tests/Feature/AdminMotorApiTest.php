<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMotorApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

    public function test_admin_motor_endpoints_require_authentication(): void
    {
        $this->getJson('/api/admin/motor')->assertStatus(401);
        $this->postJson('/api/admin/motor', [])->assertStatus(401);
    }

    public function test_admin_can_view_all_motors_with_financial_data(): void
    {
        Motor::create([
            'nama_motor' => 'Honda Beat Deluxe',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2023,
            'harga_beli' => 14000000,
            'harga_jual' => 16500000,
            'kondisi' => 'Mulus like new',
            'jarak_tempuh' => 8000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'garansi' => 'Garansi Mesin',
            'status' => 'tersedia',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/admin/motor');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.harga_beli', 14000000)
            ->assertJsonPath('data.0.potensi_keuntungan', 2500000);
    }

    public function test_admin_can_create_new_motor_with_photo_uploads(): void
    {
        $fileUtama = UploadedFile::fake()->image('beat.jpg');
        $fileGaleri1 = UploadedFile::fake()->image('beat-side.jpg');

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/admin/motor', [
                'nama_motor' => 'Yamaha Fazzio Hybrid',
                'merk' => 'Yamaha',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 17000000,
                'harga_jual' => 20000000,
                'kondisi' => 'Sangat istimewa, body mulus',
                'jarak_tempuh' => 6500,
                'surat_surat' => 'Lengkap BPKB + STNK + Faktur',
                'pajak' => 'Panjang sampai 2027',
                'garansi' => 'Garansi 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Warna neo cyan terawat sekali',
                'foto_utama' => $fileUtama,
                'photos' => [$fileGaleri1],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama_motor', 'Yamaha Fazzio Hybrid')
            ->assertJsonPath('data.harga_beli', 17000000);

        $this->assertDatabaseHas('motors', [
            'nama_motor' => 'Yamaha Fazzio Hybrid',
            'harga_beli' => 17000000,
        ]);

        $motor = Motor::where('nama_motor', 'Yamaha Fazzio Hybrid')->first();
        $this->assertNotNull($motor->foto_utama);
        Storage::disk('public')->assertExists($motor->foto_utama);
        $this->assertCount(1, $motor->photos);
    }

    public function test_admin_can_update_motor(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Suzuki Satria F150',
            'merk' => 'Suzuki',
            'tipe_motor' => 'Bebek Sport',
            'tahun' => 2021,
            'harga_beli' => 13000000,
            'harga_jual' => 15500000,
            'kondisi' => 'Mesin jos',
            'jarak_tempuh' => 22000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'tersedia',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/admin/motor/'.$motor->id, [
                'nama_motor' => 'Suzuki Satria F150 Facelift',
                'harga_jual' => 16000000,
                'status' => 'booking',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_motor', 'Suzuki Satria F150 Facelift')
            ->assertJsonPath('data.harga_jual', 16000000)
            ->assertJsonPath('data.status', 'booking');

        $this->assertDatabaseHas('motors', [
            'id' => $motor->id,
            'nama_motor' => 'Suzuki Satria F150 Facelift',
            'harga_jual' => 16000000,
            'status' => 'booking',
        ]);
    }

    public function test_admin_can_delete_motor(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Vespa Sprint 150 i-Get',
            'merk' => 'Vespa',
            'tipe_motor' => 'Matic',
            'tahun' => 2022,
            'harga_beli' => 42000000,
            'harga_jual' => 47000000,
            'kondisi' => 'Mulus kolektor',
            'jarak_tempuh' => 5000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'status' => 'tersedia',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/admin/motor/'.$motor->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('motors', [
            'id' => $motor->id,
        ]);
    }
}
