<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Motherboard
        if (Schema::hasTable('motherboards')) {
            Schema::table('motherboards', function (Blueprint $table) {
                if (Schema::hasColumn('motherboards', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('motherboards', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('motherboards', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('motherboards', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('motherboards', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // Processor
        if (Schema::hasTable('processors')) {
            Schema::table('processors', function (Blueprint $table) {
                if (Schema::hasColumn('processors', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('processors', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('processors', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('processors', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('processors', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // Penyimpanan / Hardisk
        if (Schema::hasTable('penyimpanans')) {
            Schema::table('penyimpanans', function (Blueprint $table) {
                if (Schema::hasColumn('penyimpanans', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('penyimpanans', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('penyimpanans', 'kapasitas')) {
                    $table->integer('kapasitas')->nullable()->change();
                }

                if (Schema::hasColumn('penyimpanans', 'spesifikasi')) {
                    $table->string('spesifikasi')->nullable()->change();
                }

                if (Schema::hasColumn('penyimpanans', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('penyimpanans', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('penyimpanans', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // VGA
        if (Schema::hasTable('v_g_a_s')) {
            Schema::table('v_g_a_s', function (Blueprint $table) {
                if (Schema::hasColumn('v_g_a_s', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('v_g_a_s', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('v_g_a_s', 'kapasitas')) {
                    $table->integer('kapasitas')->nullable()->change();
                }

                if (Schema::hasColumn('v_g_a_s', 'spesifikasi')) {
                    $table->string('spesifikasi')->nullable()->change();
                }

                if (Schema::hasColumn('v_g_a_s', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('v_g_a_s', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('v_g_a_s', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // RAM
        if (Schema::hasTable('r_a_m_s')) {
            Schema::table('r_a_m_s', function (Blueprint $table) {
                if (Schema::hasColumn('r_a_m_s', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('r_a_m_s', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('r_a_m_s', 'kapasitas')) {
                    $table->integer('kapasitas')->nullable()->change();
                }

                if (Schema::hasColumn('r_a_m_s', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('r_a_m_s', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('r_a_m_s', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // DVD
        if (Schema::hasTable('d_v_d_s')) {
            Schema::table('d_v_d_s', function (Blueprint $table) {
                if (Schema::hasColumn('d_v_d_s', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('d_v_d_s', 'dvd')) {
                    $table->string('dvd')->nullable()->change();
                }

                if (Schema::hasColumn('d_v_d_s', 'spesifikasi')) {
                    $table->string('spesifikasi')->nullable()->change();
                }

                if (Schema::hasColumn('d_v_d_s', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('d_v_d_s', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('d_v_d_s', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // Keyboard
        if (Schema::hasTable('keyboards')) {
            Schema::table('keyboards', function (Blueprint $table) {
                if (Schema::hasColumn('keyboards', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('keyboards', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('keyboards', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('keyboards', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('keyboards', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // Mouse
        if (Schema::hasTable('mice')) {
            Schema::table('mice', function (Blueprint $table) {
                if (Schema::hasColumn('mice', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('mice', 'tipe')) {
                    $table->string('tipe')->nullable()->change();
                }

                if (Schema::hasColumn('mice', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('mice', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('mice', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }

        // Monitor
        if (Schema::hasTable('monitors')) {
            Schema::table('monitors', function (Blueprint $table) {
                if (Schema::hasColumn('monitors', 'no_inventaris')) {
                    $table->string('no_inventaris')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'nama')) {
                    $table->string('nama')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'resolusi')) {
                    $table->string('resolusi')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'ukuran')) {
                    $table->string('ukuran')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'spesifikasi')) {
                    $table->string('spesifikasi')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'tahun')) {
                    $table->year('tahun')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'bulan')) {
                    $table->integer('bulan')->nullable()->change();
                }

                if (Schema::hasColumn('monitors', 'stok')) {
                    $table->integer('stok')->default(0)->change();
                }
            });
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan ke NOT NULL agar data lama tetap aman.
    }
};