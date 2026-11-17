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
        Schema::create('showroom_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_showroom')->default('Andara Motor');
            $table->string('no_whatsapp')->default('6281234567890');
            $table->text('alamat')->nullable();
            $table->string('jam_operasional')->nullable();
            $table->text('template_pesan_wa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_settings');
    }
};
