<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\MotorPhoto;
use App\Models\ShowroomSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMotorApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ShowroomSetting::create([
            'nama_showroom' => 'Andara Motor',
            'no_whatsapp' => '6281234567890',
            'alamat' => 'Jl. Andara Raya No. 88, Jakarta Selatan',
            'jam_operasional' => 'Senin - Sabtu 08:00 - 17:00 WIB',
            'template_pesan_wa' => 'Halo Andara Motor, saya tertarik dengan motor :nama (:tahun) dengan harga :harga. Apakah unit masih tersedia?',
        ]);

        $motor1 = Motor::create([
            'nama_motor' => 'Honda Vario 160 CBS',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2023,
            'harga_beli' => 18000000,
            'harga_jual' => 21500000,
            'kondisi' => 'Mulus 99%',
            'jarak_tempuh' => 12000,
            'surat_surat' => 'Lengkap (BPKB, STNK)',
            'pajak' => 'Hidup',
            'garansi' => 'Garansi 1 Bulan',
            'status' => 'tersedia',
            'foto_utama' => 'vario.jpg',
        ]);

        MotorPhoto::create([
            'motor_id' => $motor1->id,
            'foto_path' => 'vario-front.jpg',
            'caption' => 'Tampak Depan',
        ]);

        Motor::create([
            'nama_motor' => 'Yamaha Aerox 155 Connected',
            'merk' => 'Yamaha',
            'tipe_motor' => 'Matic',
            'tahun' => 2022,
            'harga_beli' => 21000000,
            'harga_jual' => 24500000,
            'kondisi' => 'Mesin halus',
            'jarak_tempuh' => 18000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'garansi' => 'Garansi 1 Bulan',
            'status' => 'tersedia',
            'foto_utama' => 'aerox.jpg',
        ]);

        Motor::create([
            'nama_motor' => 'Kawasaki KLX 150 BF',
            'merk' => 'Kawasaki',
            'tipe_motor' => 'Trail',
            'tahun' => 2020,
            'harga_beli' => 24000000,
            'harga_jual' => 28000000,
            'kondisi' => 'Siap trabas',
            'jarak_tempuh' => 25000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Mati 1 tahun',
            'garansi' => 'Garansi Showroom',
            'status' => 'terjual',
            'foto_utama' => 'klx.jpg',
        ]);
    }

    public function test_get_catalog_returns_only_available_motors_by_default_without_harga_beli(): void
    {
        $response = $this->getJson('/api/motor');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data'); // 2 available, 1 sold

        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertArrayNotHasKey('harga_beli', $item, 'Financial data harga_beli must never leak to public!');
            $this->assertArrayHasKey('harga_jual', $item);
            $this->assertArrayHasKey('nama_motor', $item);
        }
    }

    public function test_filter_catalog_by_search_keyword(): void
    {
        $response = $this->getJson('/api/motor?search=Aerox');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_motor', 'Yamaha Aerox 155 Connected');
    }

    public function test_filter_catalog_by_merk_and_price_range(): void
    {
        $response = $this->getJson('/api/motor?merk=Honda&harga_max=22000000');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_motor', 'Honda Vario 160 CBS');
    }

    public function test_get_motor_detail_returns_complete_info_and_whatsapp_link_without_harga_beli(): void
    {
        $motor = Motor::where('nama_motor', 'Honda Vario 160 CBS')->firstOrFail();

        $response = $this->getJson('/api/motor/' . $motor->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $motor->id)
            ->assertJsonPath('data.nama_motor', 'Honda Vario 160 CBS')
            ->assertJsonCount(1, 'data.photos');

        $detail = $response->json('data');
        $this->assertArrayNotHasKey('harga_beli', $detail, 'harga_beli must never appear in public detail');
        $this->assertArrayHasKey('whatsapp_url', $detail);
        $this->assertStringContainsString('wa.me/6281234567890', $detail['whatsapp_url']);
        $this->assertStringContainsString(urlencode('Honda Vario 160 CBS'), $detail['whatsapp_url']);
    }

    public function test_get_filter_metadata(): void
    {
        $response = $this->getJson('/api/motor/meta/filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'merks',
                    'tipe_motors',
                    'tahun_min',
                    'tahun_max',
                    'harga_min',
                    'harga_max',
                ],
            ]);
    }

    public function test_get_showroom_contact_info(): void
    {
        $response = $this->getJson('/api/kontak');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama_showroom', 'Andara Motor')
            ->assertJsonPath('data.no_whatsapp', '6281234567890');
    }
}
