<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('motors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_motor');
            $table->string('merk');
            $table->string('tipe_motor'); // Matic, Bebek, Sport, Trail, etc.
            $table->unsignedSmallInteger('tahun');
            $table->unsignedBigInteger('harga_beli'); // Modal beli showroom (hanya admin)
            $table->unsignedBigInteger('harga_jual'); // Harga jual showroom (publik)
            $table->text('kondisi');
            $table->unsignedInteger('jarak_tempuh'); // Kilometer (km)
            $table->string('surat_surat'); // Lengkap (BPKB, STNK), dsb.
            $table->string('pajak'); // Hidup (s/d bulan/tahun) / Mati
            $table->string('garansi')->nullable(); // Garansi Mesin 1 Bulan, dll.
            $table->string('status')->default('tersedia'); // tersedia, terjual, booking
            $table->string('foto_utama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index(['status', 'merk']);
            $table->index(['harga_jual', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motors');
    }
};
