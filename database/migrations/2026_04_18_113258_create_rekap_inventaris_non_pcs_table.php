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
        Schema::create('rekap_inventaris_non_pcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_inventaris_periode_id')
                ->constrained('rekap_inventaris_periodes')
                ->cascadeOnDelete();
            
            $table->string('nama_barang');
            $table->string('merk_model')->nullable();
            $table->integer('jumlah')->default(0);
            $table->string('kondisi')->default('Baik');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_inventaris_non_pcs');
    }
};
