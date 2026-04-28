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
        Schema::create('laporan_perbaikans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_inventaris_pc_id')->constrained('rekap_inventaris_pcs')->cascadeOnDelete();
            $table->foreignId('laboratorium_id')->constrained('laboratoria')->cascadeOnDelete();
            $table->string('no_pc');
            $table->string('ruang_lab');
            $table->enum('prioritas', ['Rendah', 'Sedang', 'Tinggi']);
            $table->text('keterangan')->nullable();
            $table->json('komponen_rusak')->nullable();
            $table->enum('status', ['Pending', 'Diproses', 'Selesai'])->default('Pending');
            $table->date('tanggal_pengajuan');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_perbaikans');
    }
};
