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
                    'Keyboard',
                    'Mouse',
                    'Monitor'
                ) NOT NULL
            ");
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_pc_components')) {
            return;
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
            throw $e;
        }
    }
};