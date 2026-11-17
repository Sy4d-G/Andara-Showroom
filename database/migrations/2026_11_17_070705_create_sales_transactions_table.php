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
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('motor_id')->constrained('motors');
            $table->string('nama_pembeli');
            $table->string('no_telepon_pembeli');
            $table->text('alamat_pembeli')->nullable();
            $table->date('tanggal_transaksi');
            $table->unsignedBigInteger('harga_beli');
            $table->unsignedBigInteger('harga_jual');
            $table->integer('keuntungan'); // harga_jual - harga_beli
            $table->string('metode_pembayaran')->default('Cash'); // Cash, Transfer Bank, Kredit
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tanggal_transaksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transactions');
    }
};
