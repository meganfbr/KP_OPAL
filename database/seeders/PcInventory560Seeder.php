<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryPcDetail;
use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PcInventory560Seeder extends Seeder
{
    public function run(): void
    {
        $bulan = 6;
        $tahun = 2026;

        $gudang = Laboratorium::where('ruang', 'LIKE', '%gudang%')->first();
        $gudangId = $gudang ? $gudang->id : null;

        $labsData = [
            'LAB D2A' => ['prefix' => 'A'],
            'LAB D2B' => ['prefix' => 'B'],
            'LAB D2C' => ['prefix' => 'C'],
            'LAB D2D' => ['prefix' => 'D'],
            'LAB D2E' => ['prefix' => 'E'],
            'LAB D2F' => ['prefix' => 'F'],
            'LAB D2G' => ['prefix' => 'G'],
            'LAB D2H' => ['prefix' => 'H'],
            'LAB D2I' => ['prefix' => 'I'],
            'LAB D2J' => ['prefix' => 'J'],
            'LAB D2K' => ['prefix' => 'K'],
            'LAB D3L' => ['prefix' => 'L'],
            'LAB D3M' => ['prefix' => 'M'],
            'LAB D3N' => ['prefix' => 'N'],
        ];

        // Ensure we wipe old test data for this specific scenario so we don't hit unique constraint conflicts
        // but only for New PCs (inventoriable_type = null) in the 14 labs
        $labIds = Laboratorium::whereIn('ruang', array_keys($labsData))->pluck('id');
        Inventory::whereNull('inventoriable_type')
                 ->where('bulan', $bulan)
                 ->where('tahun', $tahun)
                 ->whereIn('lokasi_id', $labIds)
                 ->delete();

        $globalCounter = 1;

        DB::beginTransaction();
        try {
            foreach ($labsData as $ruang => $data) {
                $lab = Laboratorium::where('ruang', $ruang)->first();
                if (!$lab) {
                    $this->command->warn("Laboratorium {$ruang} belum ada di database. Dilewati.");
                    $globalCounter += 40;
                    continue;
                }

                $roleName = 'Laboran_' . str_replace('LAB ', '', $ruang);
                $petugas = User::role($roleName)->first();
                $petugasId = $petugas ? $petugas->id : null;

                for ($i = 1; $i <= 40; $i++) {
                    $kodeInventaris = str_pad($globalCounter, 3, '0', STR_PAD_LEFT);
                    $noPc = $data['prefix'] . str_pad($i, 2, '0', STR_PAD_LEFT);

                    $inventory = Inventory::create([
                        'no_pc' => $noPc,
                        'lokasi_id' => $lab->id,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'inventoriable_type' => null,
                        'kode_inventaris' => $kodeInventaris,
                        'kode_unique' => null,
                        'nama_barang' => 'PC ' . $noPc,
                        'kondisi' => 'Baik',
                        'status' => 'Aktif',
                        'asal_id' => $gudangId ?: $lab->id,
                        'petugas_id' => $petugasId,
                        'laboratorium_id' => $lab->id,
                        'tanggal_pengadaan' => '2026-06-01',
                    ]);



                    $globalCounter++;
                }
                $this->command->info("Seeded 40 PC untuk {$ruang}");
            }
            DB::commit();
            $this->command->info("Total 560 PC berhasil di-seed (tanpa hardware detailed sesuai permintaan).");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Seeder gagal: " . $e->getMessage());
        }
    }
}
