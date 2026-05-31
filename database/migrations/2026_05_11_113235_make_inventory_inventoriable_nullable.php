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
            if (Schema::hasColumn('inventories', 'inventoriable_id')) {
                $table->unsignedBigInteger('inventoriable_id')
                    ->nullable()
                    ->change();
            }

            if (Schema::hasColumn('inventories', 'inventoriable_type')) {
                $table->string('inventoriable_type')
                    ->nullable()
                    ->change();
            }

            if (Schema::hasColumn('inventories', 'kode_inventaris')) {
                $table->string('kode_inventaris')
                    ->nullable()
                    ->change();
            }

            if (Schema::hasColumn('inventories', 'tanggal_pengadaan')) {
                $table->date('tanggal_pengadaan')
                    ->nullable()
                    ->change();
            }

            if (Schema::hasColumn('inventories', 'laboratorium_id')) {
                $table->unsignedBigInteger('laboratorium_id')
                    ->nullable()
                    ->change();
            }
        });
    }

    public function down(): void
    {
        /*
         * Sengaja dikosongkan agar tidak mengembalikan kolom ke NOT NULL.
         * Ini lebih aman karena konsep baru Inventaris PC tidak wajib memakai pc_details.
         */
    }
};