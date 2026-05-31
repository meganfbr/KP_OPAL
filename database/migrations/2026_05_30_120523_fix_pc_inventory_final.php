<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventories')) {
            try {
                Schema::table('inventories', function ($table) {
                    $table->dropUnique(['kode_unique']);
                });
            } catch (Throwable $e) {
                //
            }

            if (! Schema::hasColumn('inventories', 'status')) {
                Schema::table('inventories', function ($table) {
                    $table->string('status')->default('Aktif')->after('kondisi');
                });
            }

            try {
                Schema::table('inventories', function ($table) {
                    $table->unique(['bulan', 'tahun', 'kode_unique'], 'inventories_period_kode_unique_unique');
                    $table->index(['bulan', 'tahun', 'lokasi_id'], 'inventories_period_lokasi_index');
                });
            } catch (Throwable $e) {
                //
            }
        }

        if (Schema::hasTable('inventory_pc_components')) {
            try {
                DB::table('inventory_pc_components')
                    ->where('komponen', 'Mouse')
                    ->delete();

                DB::table('inventory_pc_components')
                    ->where('komponen', 'Keyboard')
                    ->update([
                        'komponen' => 'Key + Mouse',
                        'urutan' => 7,
                    ]);
            } catch (Throwable $e) {
                //
            }

            try {
                DB::statement("
                    ALTER TABLE inventory_pc_components
                    MODIFY komponen ENUM(
                        'Motherboard',
                        'Processor',
                        'Hardisk',
                        'VGA',
                        'RAM',
                        'DVD',
                        'Key + Mouse',
                        'Monitor'
                    ) NOT NULL
                ");
            } catch (Throwable $e) {
                //
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventories')) {
            try {
                Schema::table('inventories', function ($table) {
                    $table->dropUnique('inventories_period_kode_unique_unique');
                    $table->dropIndex('inventories_period_lokasi_index');
                });
            } catch (Throwable $e) {
                //
            }
        }
    }
};