<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan catatan/keterangan komponen yang bersifat spesifik per PC.
     * Ini menggantikan catatan_kondisi di rekap_inventaris_spec_details yang bersifat
     * shared (karena satu spec bisa dipakai banyak PC).
     */
    public function up(): void
    {
        Schema::create('rekap_inventaris_pc_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_inventaris_pc_id')
                ->constrained('rekap_inventaris_pcs')
                ->cascadeOnDelete();
            $table->string('komponen', 100);
            $table->text('catatan_kondisi')->nullable();
            $table->timestamps();

            // Satu PC hanya boleh punya satu catatan per komponen
            $table->unique(['rekap_inventaris_pc_id', 'komponen'], 'unique_pc_komponen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_inventaris_pc_notes');
    }
};
