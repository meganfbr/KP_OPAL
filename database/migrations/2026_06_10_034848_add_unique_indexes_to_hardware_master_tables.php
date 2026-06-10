<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->safeUnique('motherboards', ['merk', 'tipe', 'bulan', 'tahun'], 'uniq_motherboards_periode');
        $this->safeUnique('processors', ['merk', 'tipe', 'bulan', 'tahun'], 'uniq_processors_periode');
        $this->safeUnique('penyimpanans', ['merk', 'tipe', 'kapasitas', 'bulan', 'tahun'], 'uniq_penyimpanans_periode');
        $this->safeUnique('v_g_a_s', ['merk', 'tipe', 'kapasitas', 'bulan', 'tahun'], 'uniq_vgas_periode');
        $this->safeUnique('r_a_m_s', ['merk', 'tipe', 'kapasitas', 'bulan', 'tahun'], 'uniq_rams_periode');
        $this->safeUnique('d_v_d_s', ['merk', 'dvd', 'bulan', 'tahun'], 'uniq_dvds_periode');
        $this->safeUnique('keyboards', ['merk', 'tipe', 'bulan', 'tahun'], 'uniq_keyboards_periode');
        $this->safeUnique('mice', ['merk', 'tipe', 'bulan', 'tahun'], 'uniq_mice_periode');
        $this->safeUnique('monitors', ['merk', 'nama', 'ukuran', 'bulan', 'tahun'], 'uniq_monitors_periode');
    }

    public function down(): void
    {
        $this->safeDropUnique('motherboards', 'uniq_motherboards_periode');
        $this->safeDropUnique('processors', 'uniq_processors_periode');
        $this->safeDropUnique('penyimpanans', 'uniq_penyimpanans_periode');
        $this->safeDropUnique('v_g_a_s', 'uniq_vgas_periode');
        $this->safeDropUnique('r_a_m_s', 'uniq_rams_periode');
        $this->safeDropUnique('d_v_d_s', 'uniq_dvds_periode');
        $this->safeDropUnique('keyboards', 'uniq_keyboards_periode');
        $this->safeDropUnique('mice', 'uniq_mice_periode');
        $this->safeDropUnique('monitors', 'uniq_monitors_periode');
    }

    protected function safeUnique(string $tableName, array $columns, string $indexName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->unique($columns, $indexName);
            });
        } catch (\Throwable $e) {
            //
        }
    }

    protected function safeDropUnique(string $tableName, string $indexName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable $e) {
            //
        }
    }
};