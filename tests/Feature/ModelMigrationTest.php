<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\MotorPhoto;
use App\Models\SalesTransaction;
use App\Models\ShowroomSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_username_role_and_api_tokens(): void
    {
        $user = User::create([
            'name' => 'Admin Andara',
            'username' => 'admin',
            'email' => 'admin@andaramotor.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@andaramotor.com',
            'username' => 'admin',
            'role' => 'admin',
        ]);

        $token = $user->createToken('test-token');
        $this->assertNotEmpty($token->plainTextToken);
    }

    public function test_motor_and_photos_relationship(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Honda Vario 160 CBS',
            'merk' => 'Honda',
            'tipe_motor' => 'Matic',
            'tahun' => 2023,
            'harga_beli' => 18000000,
            'harga_jual' => 21500000,
            'kondisi' => 'Mulus 98%, ban tebal, mesin standar halus',
            'jarak_tempuh' => 12500,
            'surat_surat' => 'Lengkap (BPKB, STNK, Faktur)',
            'pajak' => 'Hidup (s/d November 2026)',
            'garansi' => 'Garansi Mesin 1 Bulan',
            'status' => 'tersedia',
            'foto_utama' => 'motors/vario-160.jpg',
            'deskripsi' => 'Motor terawat milik tangan pertama dari baru.',
        ]);

        $this->assertDatabaseHas('motors', [
            'nama_motor' => 'Honda Vario 160 CBS',
            'merk' => 'Honda',
            'status' => 'tersedia',
        ]);

        $photo1 = MotorPhoto::create([
            'motor_id' => $motor->id,
            'foto_path' => 'motors/photos/vario-front.jpg',
            'caption' => 'Tampak Depan',
        ]);

        $this->assertCount(1, $motor->photos);
        $this->assertEquals($motor->id, $photo1->motor->id);
    }

    public function test_motor_and_sales_transaction_relationship(): void
    {
        $motor = Motor::create([
            'nama_motor' => 'Yamaha NMAX 155 Connected',
            'merk' => 'Yamaha',
            'tipe_motor' => 'Matic',
            'tahun' => 2022,
            'harga_beli' => 23000000,
            'harga_jual' => 26500000,
            'kondisi' => 'Sangat terawat, servis rutin',
            'jarak_tempuh' => 18000,
            'surat_surat' => 'Lengkap',
            'pajak' => 'Hidup',
            'garansi' => 'Garansi Showroom 30 Hari',
            'status' => 'terjual',
        ]);

        $transaction = SalesTransaction::create([
            'kode_transaksi' => 'TRX-20261117-001',
            'motor_id' => $motor->id,
            'nama_pembeli' => 'Budi Santoso',
            'no_telepon_pembeli' => '08123456789',
            'alamat_pembeli' => 'Jl. Merdeka No. 10, Karawang',
            'tanggal_transaksi' => '2026-11-17',
            'harga_beli' => 23000000,
            'harga_jual' => 26500000,
            'keuntungan' => 3500000,
            'metode_pembayaran' => 'Cash',
            'catatan' => 'Lunas seketika di showroom',
        ]);

        $this->assertDatabaseHas('sales_transactions', [
            'kode_transaksi' => 'TRX-20261117-001',
            'keuntungan' => 3500000,
        ]);

        $this->assertEquals($motor->id, $transaction->motor->id);
        $this->assertEquals($transaction->id, $motor->salesTransaction->id);
    }

    public function test_showroom_setting_model(): void
    {
        $setting = ShowroomSetting::create([
            'nama_showroom' => 'Andara Motor',
            'no_whatsapp' => '6281234567890',
            'alamat' => 'Jl. Andara Raya No. 88, Jakarta Selatan',
            'jam_operasional' => 'Senin - Sabtu (08.00 - 17.00)',
            'template_pesan_wa' => 'Halo Andara Motor, saya tertarik dengan motor ini.',
        ]);

        $this->assertDatabaseHas('showroom_settings', [
            'nama_showroom' => 'Andara Motor',
            'no_whatsapp' => '6281234567890',
        ]);
    }
}
