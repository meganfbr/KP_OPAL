<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekap_inventaris_specs', function (Blueprint $table) {
            $table->string('kondisi_pc')->nullable()->after('fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_inventaris_specs', function (Blueprint $table) {
            $table->dropColumn('kondisi_pc');
        });
    }
};
