<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InventarisPcSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(InventoryPcMei2026Seeder::class);

        $this->command?->info('Inventaris PC berhasil dibuat melalui InventoryPcMei2026Seeder.');
    }
}