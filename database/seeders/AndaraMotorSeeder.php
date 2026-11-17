<?php

namespace Database\Seeders;

use App\Models\Motor;
use App\Models\MotorPhoto;
use App\Models\SalesTransaction;
use App\Models\ShowroomSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AndaraMotorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        User::updateOrCreate(
            ['email' => 'admin@andaramotor.com'],
            [
                'name' => 'Mohamed Ali Irsyad Ginting',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Showroom Settings
        ShowroomSetting::updateOrCreate(
            ['id' => 1],
            [
                'nama_showroom' => 'Andara Motor',
                'no_whatsapp' => '6281234567890',
                'alamat' => 'Jl. Andara Raya No. 88, Pangkalan Jati, Kec. Cinere, Kota Depok, Jawa Barat 16513',
                'jam_operasional' => 'Senin - Sabtu: 08:30 - 17:30 WIB, Minggu: 09:00 - 15:00 WIB',
                'template_pesan_wa' => 'Halo Showroom Andara Motor, saya tertarik dengan motor :nama (Tahun :tahun) dengan harga :harga. Apakah unit ini masih tersedia untuk cek unit / negosiasi?',
            ]
        );

        // 3. Realistic Motor Data
        $motorsData = [
            [
                'nama_motor' => 'Honda Vario 160 CBS Active Black',
                'merk' => 'Honda',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 18500000,
                'harga_jual' => 22000000,
                'kondisi' => 'Mulus 98%, bodi kinclong terpasang PPF, ban tebal 90%, mesin halus tarikan mantap.',
                'jarak_tempuh' => 11200,
                'surat_surat' => 'Lengkap (BPKB, STNK, Faktur, Sertifikat NIK)',
                'pajak' => 'Hidup (s/d Agustus 2026, Plat B Depok)',
                'garansi' => 'Garansi Mesin & Transmisi 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Tangan pertama dari baru, servis resmi berkala di bengkel AHASS, kunci cadangan remote lengkap.',
                'photos' => [
                    'Tampak Depan Headlamp LED',
                    'Speedometer Digital & Stang',
                    'Bagasi Luas & Mesin eSP+',
                ],
            ],
            [
                'nama_motor' => 'Honda Beat Deluxe CBS-ISS Matte Blue',
                'merk' => 'Honda',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 13500000,
                'harga_jual' => 16300000,
                'kondisi' => 'Istimewa rawatan harian, ban depan-belakang tubeless tebal, kelistrikan normal.',
                'jarak_tempuh' => 8400,
                'surat_surat' => 'Lengkap (BPKB, STNK, Faktur)',
                'pajak' => 'Hidup (s/d November 2026, Plat B Jaksel)',
                'garansi' => 'Garansi Showroom 30 Hari',
                'status' => 'tersedia',
                'deskripsi' => 'Motor sangat irit BBM hingga 60 km/liter, cocok untuk komuter harian di perkotaan.',
                'photos' => [
                    'Tampak Samping Bodi Matte Blue',
                    'Ban Tubeless & Velg Standar',
                ],
            ],
            [
                'nama_motor' => 'Honda Scoopy Prestige Smartkey White',
                'merk' => 'Honda',
                'tipe_motor' => 'Matic',
                'tahun' => 2022,
                'harga_beli' => 16800000,
                'harga_jual' => 19900000,
                'kondisi' => 'Sangat mulus retro elegan, mesin kering tidak rembes, aki sehat jreng.',
                'jarak_tempuh' => 14200,
                'surat_surat' => 'Lengkap (BPKB, STNK, Buku Garansi)',
                'pajak' => 'Hidup (s/d Mei 2026)',
                'garansi' => 'Garansi Mesin 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Varian tertinggi Prestige dengan Smart Key System remote, velg bronze original mulus.',
                'photos' => [
                    'Tampak Depan Lampu Projector LED',
                    'Dashboard Smartkey & Charger Port',
                ],
            ],
            [
                'nama_motor' => 'Honda PCX 160 ABS Brilliant Black',
                'merk' => 'Honda',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 28000000,
                'harga_jual' => 32500000,
                'kondisi' => 'Like new 99%, rem ABS normal pakem, HSTC (kontrol traksi) berfungsi sempurna.',
                'jarak_tempuh' => 9100,
                'surat_surat' => 'Lengkap (BPKB, STNK, Faktur asli)',
                'pajak' => 'Hidup (s/d Oktober 2026)',
                'garansi' => 'Garansi Mesin 1 Bulan & Servis Gratis 1x',
                'status' => 'tersedia',
                'deskripsi' => 'Motor maxi scooter premium, suspensi sangat nyaman untuk touring maupun mobilitas kerja.',
                'photos' => [
                    'Tampak Depan Windshield & Dual Headlamp',
                    'Panel Meter Full Digital Negatif',
                    'Cakram Depan Sistem ABS',
                ],
            ],
            [
                'nama_motor' => 'Yamaha NMAX 155 Connected ABS Matte Dark Green',
                'merk' => 'Yamaha',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 26500000,
                'harga_jual' => 30800000,
                'kondisi' => 'Kondisi prima, bodi original mulus, mesin 155cc VVA bertenaga responsif.',
                'jarak_tempuh' => 12300,
                'surat_surat' => 'Lengkap BPKB, STNK, Buku Servis',
                'pajak' => 'Hidup (s/d September 2026)',
                'garansi' => 'Garansi Mesin 30 Hari',
                'status' => 'tersedia',
                'deskripsi' => 'Bisa terkoneksi ke smartphone via aplikasi Y-Connect, shockbreaker belakang tabung original.',
                'photos' => [
                    'Tampak Depan Elegan Matte Green',
                    'Suspensi Sub-tank Belakang Gold',
                ],
            ],
            [
                'nama_motor' => 'Yamaha Aerox 155 Connected Cyber City',
                'merk' => 'Yamaha',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 22000000,
                'harga_jual' => 25900000,
                'kondisi' => 'Sporty abis, warna bunglon spesial Cyber City terawat, ban belakang tapak lebar tebal.',
                'jarak_tempuh' => 10500,
                'surat_surat' => 'Lengkap (BPKB + STNK)',
                'pajak' => 'Hidup (s/d Juli 2026)',
                'garansi' => 'Garansi Showroom 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Karakter racing sporty, mesin VVA 155cc tarikan enteng, ban tebal 95%.',
                'photos' => [
                    'Livery Cyber City Bunglon',
                    'Speedometer Sporty & Handle Bar',
                ],
            ],
            [
                'nama_motor' => 'Yamaha Fazzio Lux Hybrid White Pearl',
                'merk' => 'Yamaha',
                'tipe_motor' => 'Matic',
                'tahun' => 2022,
                'harga_beli' => 15500000,
                'harga_jual' => 18700000,
                'kondisi' => 'Mulus cantik, jok kulit cokelat premium original Lux, hybrid assist aktif lancar.',
                'jarak_tempuh' => 15200,
                'surat_surat' => 'Lengkap',
                'pajak' => 'Hidup (s/d April 2026)',
                'garansi' => 'Garansi Mesin 1 Bulan',
                'status' => 'booking',
                'deskripsi' => 'Desain classy retro, konsumsi BBM super irit didukung Blue Core Hybrid.',
                'photos' => [
                    'Desain Retro Modern White Pearl',
                ],
            ],
            [
                'nama_motor' => 'Kawasaki Ninja 250 FI SE ABS KRT Edition',
                'merk' => 'Kawasaki',
                'tipe_motor' => 'Sport',
                'tahun' => 2021,
                'harga_beli' => 43000000,
                'harga_jual' => 49500000,
                'kondisi' => 'Kondisi istimewa simpanan, knalpot standar ada, mesin 2 silinder DOHC padat merdu.',
                'jarak_tempuh' => 13400,
                'surat_surat' => 'Lengkap BPKB + STNK + Faktur',
                'pajak' => 'Hidup (s/d Desember 2026, Plat B)',
                'garansi' => 'Garansi Mesin 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Livery Kawasaki Racing Team (KRT) original pabrik, frame slider terpasang rapi.',
                'photos' => [
                    'Fairing Samping KRT Green',
                    'Mesin 2 Silinder DOHC Twin Exhaust',
                ],
            ],
            [
                'nama_motor' => 'Kawasaki KLX 150 BF SE Extreme',
                'merk' => 'Kawasaki',
                'tipe_motor' => 'Trail',
                'tahun' => 2020,
                'harga_beli' => 22500000,
                'harga_jual' => 26800000,
                'kondisi' => 'Kaki-kaki empuk upside down gold original, ban pacul tebal, siap harian & trabas.',
                'jarak_tempuh' => 21300,
                'surat_surat' => 'Lengkap BPKB, STNK',
                'pajak' => 'Mati 1 Tahun (bisa dibantu perpanjang)',
                'garansi' => 'Garansi Showroom 14 Hari',
                'status' => 'tersedia',
                'deskripsi' => 'Suspensi depan USD (Up-Side Down) emas, stang fatbar dan handguard original.',
                'photos' => [
                    'Suspensi Upside Down Gold & Roda Pacul',
                ],
            ],
            [
                'nama_motor' => 'Honda CRF 150L Extreme Black',
                'merk' => 'Honda',
                'tipe_motor' => 'Trail',
                'tahun' => 2021,
                'harga_beli' => 23500000,
                'harga_jual' => 27500000,
                'kondisi' => 'Standar dealer, bodi full orisinil, mesin injeksi PGM-FI bandel dan irit.',
                'jarak_tempuh' => 16500,
                'surat_surat' => 'Lengkap (BPKB, STNK)',
                'pajak' => 'Hidup (s/d Maret 2026)',
                'garansi' => 'Garansi Mesin 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Velg aluminium warna hitam original, speedometer digital tajam.',
                'photos' => [
                    'Tampak Depan Shroud CRF150L',
                ],
            ],
            [
                'nama_motor' => 'Vespa Primavera 150 i-Get ABS Relax Green',
                'merk' => 'Vespa',
                'tipe_motor' => 'Matic',
                'tahun' => 2022,
                'harga_beli' => 41000000,
                'harga_jual' => 46800000,
                'kondisi' => 'Koleksi pribadi super mulus, cat original Relax Green (sage green) kinclong.',
                'jarak_tempuh' => 7800,
                'surat_surat' => 'Lengkap Faktur, Form A, BPKB, STNK',
                'pajak' => 'Hidup (s/d Januari 2026)',
                'garansi' => 'Garansi Showroom 1 Bulan',
                'status' => 'booking',
                'deskripsi' => 'Kunci biru dan master cokelat lengkap, bodi lempeng no spet no insiden.',
                'photos' => [
                    'Warna Relax Green Ikonik Vespa',
                ],
            ],
            [
                'nama_motor' => 'Vespa Sprint 150 S i-Get ABS Yellow Sole',
                'merk' => 'Vespa',
                'tipe_motor' => 'Matic',
                'tahun' => 2021,
                'harga_beli' => 42000000,
                'harga_jual' => 47900000,
                'kondisi' => 'Body mulus istimewa, headlamp heksagonal khas Sprint gagah, ban Pirelli tebal.',
                'jarak_tempuh' => 11800,
                'surat_surat' => 'Lengkap BPKB, STNK',
                'pajak' => 'Hidup (s/d Agustus 2026)',
                'garansi' => 'Garansi Mesin 30 Hari',
                'status' => 'tersedia',
                'deskripsi' => 'Warna kuning doff Yellow Sole favorit, emblem dan stiker S merah sporty.',
                'photos' => [
                    'Headlamp Heksagonal Sprint Yellow Sole',
                ],
            ],
            [
                'nama_motor' => 'Suzuki Satria F150 All New Fi Titan Black',
                'merk' => 'Suzuki',
                'tipe_motor' => 'Bebek Sport',
                'tahun' => 2021,
                'harga_beli' => 13200000,
                'harga_jual' => 16200000,
                'kondisi' => 'Mesin standar no bore-up, tenaga 18.2 HP sangat kencang, bodi mulus.',
                'jarak_tempuh' => 19400,
                'surat_surat' => 'Lengkap',
                'pajak' => 'Hidup (s/d Februari 2026)',
                'garansi' => 'Garansi Mesin 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Raja ayago underbone 150cc DOHC 4 valve pendingin radiator, siap gas.',
                'photos' => [
                    'Ayago Sporty Satria F150',
                ],
            ],
            [
                'nama_motor' => 'Yamaha WR 155 R Yamaha Blue',
                'merk' => 'Yamaha',
                'tipe_motor' => 'Trail',
                'tahun' => 2022,
                'harga_beli' => 27000000,
                'harga_jual' => 31200000,
                'kondisi' => 'Motor jangkung bertenaga, radiator liquid cooling normal, ban dual purpose tebal.',
                'jarak_tempuh' => 11400,
                'surat_surat' => 'Lengkap',
                'pajak' => 'Hidup (s/d Juni 2026)',
                'garansi' => 'Garansi Mesin 1 Bulan',
                'status' => 'tersedia',
                'deskripsi' => 'Mesin 155cc VVA berpendingin cairan, tangki 8.1 liter kapasitas terbesar di kelasnya.',
                'photos' => [
                    'Yamaha Blue Trail Adventure WR155',
                ],
            ],
            // Sold Motors with Sales Transactions attached below
            [
                'nama_motor' => 'Yamaha Grand Filano Neo Dull Blue',
                'merk' => 'Yamaha',
                'tipe_motor' => 'Matic',
                'tahun' => 2023,
                'harga_beli' => 19000000,
                'harga_jual' => 22500000,
                'kondisi' => 'Kondisi mulus orisinil terawat',
                'jarak_tempuh' => 8900,
                'surat_surat' => 'Lengkap BPKB, STNK',
                'pajak' => 'Hidup',
                'garansi' => 'Garansi 1 Bulan',
                'status' => 'terjual',
                'deskripsi' => 'Desain retro eropa dengan panel instrumen TFT sub-display canggih.',
                'photos' => [],
            ],
            [
                'nama_motor' => 'Honda Supra GTR 150 Exclusive Matte Red',
                'merk' => 'Honda',
                'tipe_motor' => 'Bebek Sport',
                'tahun' => 2020,
                'harga_beli' => 11800000,
                'harga_jual' => 14700000,
                'kondisi' => 'Mesin sehat DOHC 6 percepatan, siap jarak jauh',
                'jarak_tempuh' => 28400,
                'surat_surat' => 'Lengkap BPKB, STNK',
                'pajak' => 'Hidup',
                'garansi' => 'Garansi 14 Hari',
                'status' => 'terjual',
                'deskripsi' => 'Bebek penjelajah 150cc 6-speed kopling manual, ban tubeless tapak lebar.',
                'photos' => [],
            ],
        ];

        foreach ($motorsData as $m) {
            $photos = $m['photos'] ?? [];
            unset($m['photos']);

            $motor = Motor::create($m);

            foreach ($photos as $caption) {
                MotorPhoto::create([
                    'motor_id' => $motor->id,
                    'foto_path' => 'motors/sample-'.$motor->id.'.jpg',
                    'caption' => $caption,
                ]);
            }
        }

        // 4. Create Historical Sales Transactions for Sold Motors
        $soldFilano = Motor::where('nama_motor', 'like', '%Grand Filano%')->first();
        if ($soldFilano) {
            SalesTransaction::updateOrCreate(
                ['kode_transaksi' => 'TRX-20261015-001'],
                [
                    'motor_id' => $soldFilano->id,
                    'nama_pembeli' => 'Dimas Prasetyo',
                    'no_telepon_pembeli' => '081288776655',
                    'alamat_pembeli' => 'Jl. Margonda Raya No. 45, Beji, Depok',
                    'tanggal_transaksi' => Carbon::parse('2026-10-15'),
                    'harga_beli' => $soldFilano->harga_beli,
                    'harga_jual' => $soldFilano->harga_jual,
                    'keuntungan' => $soldFilano->harga_jual - $soldFilano->harga_beli,
                    'metode_pembayaran' => 'Transfer Bank',
                    'catatan' => 'Pembeli transfer lunas via BCA. Pengiriman unit ke Depok.',
                ]
            );
        }

        $soldSupra = Motor::where('nama_motor', 'like', '%Supra GTR%')->first();
        if ($soldSupra) {
            SalesTransaction::updateOrCreate(
                ['kode_transaksi' => 'TRX-20261102-002'],
                [
                    'motor_id' => $soldSupra->id,
                    'nama_pembeli' => 'Agus Wicaksono',
                    'no_telepon_pembeli' => '085712349876',
                    'alamat_pembeli' => 'Jl. Cilandak KKO No. 12, Jakarta Selatan',
                    'tanggal_transaksi' => Carbon::parse('2026-11-02'),
                    'harga_beli' => $soldSupra->harga_beli,
                    'harga_jual' => $soldSupra->harga_jual,
                    'keuntungan' => $soldSupra->harga_jual - $soldSupra->harga_beli,
                    'metode_pembayaran' => 'Cash',
                    'catatan' => 'Pembayaran tunai di kasir showroom Andara Motor.',
                ]
            );
        }
    }
}
