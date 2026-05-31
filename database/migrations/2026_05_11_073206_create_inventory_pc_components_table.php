<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_pc_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')
                ->constrained('inventories')
                ->cascadeOnDelete();

            $table->enum('komponen', [
                'Motherboard',
                'Processor',
                'Hardisk',
                'VGA',
                'RAM',
                'DVD',
                'Key + Mouse',
                'Monitor',
            ]);

            $table->foreignId('motherboard_id')
                ->nullable()
                ->constrained('motherboards')
                ->nullOnDelete();

            $table->foreignId('processor_id')
                ->nullable()
                ->constrained('processors')
                ->nullOnDelete();

            $table->foreignId('penyimpanan_id')
                ->nullable()
                ->constrained('penyimpanans')
                ->nullOnDelete();

            $table->foreignId('vga_id')
                ->nullable()
                ->constrained('v_g_a_s')
                ->nullOnDelete();

            $table->foreignId('ram_id')
                ->nullable()
                ->constrained('r_a_m_s')
                ->nullOnDelete();

            $table->foreignId('dvd_id')
                ->nullable()
                ->constrained('d_v_d_s')
                ->nullOnDelete();

            $table->foreignId('keyboard_id')
                ->nullable()
                ->constrained('keyboards')
                ->nullOnDelete();

            $table->foreignId('mouse_id')
                ->nullable()
                ->constrained('mice')
                ->nullOnDelete();

            $table->foreignId('monitor_id')
                ->nullable()
                ->constrained('monitors')
                ->nullOnDelete();

            $table->enum('kondisi', [
                'Baik',
                'Kurang Baik',
                'Rusak',
                '-',
            ])->default('-');

            $table->text('keterangan')->nullable();

            $table->unsignedTinyInteger('urutan')->default(1);

            $table->timestamps();

            $table->unique(['inventory_id', 'komponen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_pc_components');
    }
};