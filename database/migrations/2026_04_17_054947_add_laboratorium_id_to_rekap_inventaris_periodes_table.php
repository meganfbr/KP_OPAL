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
        Schema::table('rekap_inventaris_periodes', function (Blueprint $table) {
            $table->foreignId('laboratorium_id')->nullable()->after('id')->constrained('laboratoria')->cascadeOnDelete();
            
            // Drop old unique constraint
            $table->dropUnique(['bulan', 'tahun']);
        });

        // Assign legacy data to first lab if any
        $firstLabId = DB::table('laboratoria')->value('id');
        if ($firstLabId) {
            DB::table('rekap_inventaris_periodes')->update(['laboratorium_id' => $firstLabId]);
        }

        Schema::table('rekap_inventaris_periodes', function (Blueprint $table) {
            $table->unique(['bulan', 'tahun', 'laboratorium_id'], 'rekap_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rekap_inventaris_periodes', function (Blueprint $table) {
            $table->dropUnique('rekap_periode_unique');
            $table->dropForeign(['laboratorium_id']);
            $table->dropColumn('laboratorium_id');
            $table->unique(['bulan', 'tahun']);
        });
    }
};
