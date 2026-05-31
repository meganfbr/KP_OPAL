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
            if (! Schema::hasColumn('inventories', 'kode_unique')) {
                $table->string('kode_unique')
                    ->nullable()
                    ->unique()
                    ->after('id');
            }

            if (! Schema::hasColumn('inventories', 'no_pc')) {
                $table->string('no_pc')
                    ->nullable()
                    ->after('kode_unique');
            }

            /*
             * Jangan pakai foreign key dulu di sini,
             * karena nama tabel lab di project kamu belum dipastikan.
             */
            if (! Schema::hasColumn('inventories', 'asal_id')) {
                $table->unsignedBigInteger('asal_id')
                    ->nullable()
                    ->after('no_pc');
            }

            if (! Schema::hasColumn('inventories', 'lokasi_id')) {
                $table->unsignedBigInteger('lokasi_id')
                    ->nullable()
                    ->after('asal_id');
            }

            if (! Schema::hasColumn('inventories', 'petugas_id')) {
                $table->unsignedBigInteger('petugas_id')
                    ->nullable()
                    ->after('lokasi_id');
            }

            if (! Schema::hasColumn('inventories', 'kondisi')) {
                $table->string('kondisi')
                    ->default('Baik')
                    ->after('petugas_id');
            }
        });

        /*
         * Field lama dibuat nullable agar form Inventaris PC baru
         * tidak wajib mengisi field lama seperti tanggal_pengadaan.
         */
        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'tanggal_pengadaan')) {
                $table->date('tanggal_pengadaan')->nullable()->change();
            }

            if (Schema::hasColumn('inventories', 'kode_inventaris')) {
                $table->string('kode_inventaris')->nullable()->change();
            }

            if (Schema::hasColumn('inventories', 'laboratorium_id')) {
                $table->unsignedBigInteger('laboratorium_id')->nullable()->change();
            }

            if (Schema::hasColumn('inventories', 'tahun')) {
                $table->year('tahun')->nullable()->change();
            }

            if (Schema::hasColumn('inventories', 'bulan')) {
                $table->integer('bulan')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasColumn('inventories', 'kode_unique')) {
                $table->dropUnique(['kode_unique']);
                $table->dropColumn('kode_unique');
            }

            if (Schema::hasColumn('inventories', 'no_pc')) {
                $table->dropColumn('no_pc');
            }

            if (Schema::hasColumn('inventories', 'asal_id')) {
                $table->dropColumn('asal_id');
            }

            if (Schema::hasColumn('inventories', 'lokasi_id')) {
                $table->dropColumn('lokasi_id');
            }

            if (Schema::hasColumn('inventories', 'petugas_id')) {
                $table->dropColumn('petugas_id');
            }

            if (Schema::hasColumn('inventories', 'kondisi')) {
                $table->dropColumn('kondisi');
            }
        });
    }
};