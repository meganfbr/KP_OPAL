<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_pc_components')) {
            return;
        }

        /*
         * Ubah enum agar Keyboard dan Mouse terpisah.
         */
        DB::statement("
            ALTER TABLE inventory_pc_components
            MODIFY komponen ENUM(
                'Motherboard',
                'Processor',
                'Hardisk',
                'VGA',
                'RAM',
                'DVD',
                'Keyboard',
                'Mouse',
                'Monitor'
            ) NOT NULL
        ");

        /*
         * Jika ada data lama Key + Mouse, pisahkan secara sederhana.
         * Karena tabel kamu tadi masih kosong, kemungkinan bagian ini tidak berpengaruh.
         */
        DB::table('inventory_pc_components')
            ->where('komponen', 'Key + Mouse')
            ->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_pc_components')) {
            return;
        }

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
    }
};