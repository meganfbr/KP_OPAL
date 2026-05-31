<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventories')) {
            return;
        }

        Schema::table('inventories', function (Blueprint $table) {
            if (! Schema::hasColumn('inventories', 'bulan')) {
                $table->unsignedTinyInteger('bulan')
                    ->nullable()
                    ->after('kondisi');
            }

            if (! Schema::hasColumn('inventories', 'tahun')) {
                $table->unsignedSmallInteger('tahun')
                    ->nullable()
                    ->after('bulan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'bulan')) {
                $table->dropColumn('bulan');
            }

            if (Schema::hasColumn('inventories', 'tahun')) {
                $table->dropColumn('tahun');
            }
        });
    }
};