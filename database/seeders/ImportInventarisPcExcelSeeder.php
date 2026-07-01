<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryPcComponent;
use App\Models\InventoryPcDetail;
use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportInventarisPcExcelSeeder extends Seeder
{
    public function run(): void
    {
        $excelPath = database_path('imports/template_import_inventaris_pc_siopal_560.xlsx');

        if (!file_exists($excelPath)) {
            $this->command->warn("File Excel Inventaris PC tidak ditemukan di: {$excelPath}. Import dilewati.");
            return;
        }

        $this->command->info("Mengkonversi data Inventaris PC dari Excel...");

        $pythonScript = "
import pandas as pd
import json
import sys

try:
    df = pd.read_excel(r'" . addslashes($excelPath) . "', header=None)
    header_idx = None
    for i, row in df.iterrows():
        row_str = ' '.join([str(v) for v in row.values if pd.notna(v)])
        if 'No PC' in row_str and 'Laboratorium' in row_str:
            header_idx = i
            break
            
    if header_idx is not None:
        df.columns = df.iloc[header_idx]
        df = df.iloc[header_idx+1:].reset_index(drop=True)
    else:
        df = pd.read_excel(r'" . addslashes($excelPath) . "', skiprows=3)

    if len(df) == 0:
        raise Exception('Jumlah row terbaca 0. Harap periksa template Excel Anda.')

    data = []
    
    def get_val(r, col_name, default=None):
        if col_name in df.columns:
            val = r[col_name]
            if pd.isna(val) or str(val).strip().lower() == 'nan':
                return default
            return str(val).strip()
        return default

    for i, row in df.iterrows():
        no_pc = get_val(row, 'No PC')
        if not no_pc or no_pc == '-':
            continue
            
        hardware = {
            'Motherboard': get_val(row, 'Motherboard', '-'),
            'Processor': get_val(row, 'Processor', '-'),
            'RAM': get_val(row, 'RAM', '-'),
            'Penyimpanan': get_val(row, 'Penyimpanan', '-'),
            'VGA': get_val(row, 'VGA', '-'),
            'PSU': get_val(row, 'PSU', '-'),
            'Keyboard': get_val(row, 'Keyboard', '-'),
            'Mouse': get_val(row, 'Mouse', '-'),
            'Monitor': get_val(row, 'Monitor', '-'),
            'DVD': get_val(row, 'DVD', '-'),
            'Headphone': get_val(row, 'Headphone', '-'),
        }

        data.append({
            'id': get_val(row, 'ID'),
            'no_pc': no_pc,
            'laboratorium': get_val(row, 'Laboratorium'),
            'posisi': get_val(row, 'Posisi', 'Client'),
            'kode_bium': get_val(row, 'Kode BIUM'),
            'petugas': get_val(row, 'Petugas'),
            'asal': get_val(row, 'Asal'),
            'hardware': hardware,
            'kondisi': get_val(row, 'Kondisi PC', 'Baik'),
            'bulan': get_val(row, 'Bulan', 6),
            'tahun': get_val(row, 'Tahun', 2026),
        })

    if len(data) == 0:
        raise Exception('Jumlah row terbaca 0 setelah filter (No PC kosong). Harap isi No PC.')

    print(json.dumps(data))

except Exception as e:
    sys.stderr.write(str(e))
    sys.exit(1)
";

        $tempPy = tempnam(sys_get_temp_dir(), 'import_pc');
        file_put_contents($tempPy, $pythonScript);
        $jsonOutput = shell_exec("python " . escapeshellarg($tempPy) . " 2>&1");
        unlink($tempPy);

        $data = json_decode(trim($jsonOutput), true);

        if (!$data || !is_array($data)) {
            $this->command->error("Gagal mengkonversi Excel Inventaris PC atau data kosong.");
            $this->command->error("Log: " . $jsonOutput);
            return;
        }

        $this->command->info("Memproses " . count($data) . " item Inventaris PC...");

        $successCount = 0;

        foreach ($data as $item) {
            $lab = Laboratorium::where('ruang', $item['laboratorium'])->first();
            $asal = Laboratorium::where('ruang', $item['asal'])->first();

            // Handle petugas if not placeholder
            $petugasId = null;
            if ($item['petugas'] && !str_contains($item['petugas'], 'Isi Nama')) {
                $petugas = User::where('name', 'LIKE', '%' . $item['petugas'] . '%')
                    ->orWhere('npp', $item['petugas'])
                    ->first();
                $petugasId = $petugas?->id;
            }

            $bulan = (int) $item['bulan'];
            $tahun = (int) $item['tahun'];
            
            // Menggunakan ID dari Excel. Jika tidak ada, hindari penggunaan i+1 (index array excel)
            // Sebaliknya, gunakan urutan successCount agar konsisten meskipun ada row yang diskip.
            // Atau cukup ambil angka unik agar constraint database tidak bentrok.
            $kodeInventaris = $item['id'];
            if (!$kodeInventaris || $kodeInventaris === '-' || $kodeInventaris === 'nan') {
                $kodeInventaris = str_pad($successCount + 1, 3, '0', STR_PAD_LEFT);
            }
            
            $kodeBium = $item['kode_bium'] === '-' ? null : $item['kode_bium'];

            DB::transaction(function () use ($item, $lab, $asal, $petugasId, $bulan, $tahun, $kodeInventaris, $kodeBium, &$successCount) {
                // 1. Inventories
                $inventory = Inventory::updateOrCreate(
                    [
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'lokasi_id' => $lab?->id,
                        'no_pc' => $item['no_pc'],
                    ],
                    [
                        'kode_inventaris' => $kodeInventaris,
                        'kode_unique' => $kodeBium,
                        'nama_barang' => 'PC ' . $item['no_pc'],
                        'kondisi' => $item['kondisi'],
                        'status' => 'Aktif',
                        'laboratorium_id' => $lab?->id,
                        'asal_id' => $asal?->id ?: $lab?->id,
                        'petugas_id' => $petugasId,
                        'tanggal_pengadaan' => $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01',
                        'inventoriable_id' => null,
                        'inventoriable_type' => null,
                    ]
                );

                // 2. Inventory PC Details
                InventoryPcDetail::updateOrCreate(
                    ['inventory_id' => $inventory->id],
                    ['posisi' => $item['posisi']]
                );

                // 3. Inventory PC Components
                $hardware = $item['hardware'];
                $mappings = [
                    'Motherboard' => ['model' => \App\Models\Motherboard::class, 'fk' => 'motherboard_id', 'val' => $hardware['Motherboard']],
                    'Processor' => ['model' => \App\Models\Processor::class, 'fk' => 'processor_id', 'val' => $hardware['Processor']],
                    'RAM' => ['model' => \App\Models\RAM::class, 'fk' => 'ram_id', 'val' => $hardware['RAM']],
                    'Hardisk' => ['model' => \App\Models\Penyimpanan::class, 'fk' => 'penyimpanan_id', 'val' => $hardware['Penyimpanan']],
                    'VGA' => ['model' => \App\Models\VGA::class, 'fk' => 'vga_id', 'val' => $hardware['VGA']],
                    'Keyboard' => ['model' => \App\Models\Keyboard::class, 'fk' => 'keyboard_id', 'val' => $hardware['Keyboard']],
                    'Mouse' => ['model' => \App\Models\Mouse::class, 'fk' => 'mouse_id', 'val' => $hardware['Mouse']],
                    'Monitor' => ['model' => \App\Models\Monitor::class, 'fk' => 'monitor_id', 'val' => $hardware['Monitor']],
                    'DVD' => ['model' => \App\Models\DVD::class, 'fk' => 'dvd_id', 'val' => $hardware['DVD']],
                ];

                $urutan = 1;
                foreach ($mappings as $komponen => $config) {
                    $hwId = $this->getHardwareId($config['model'], $config['val'], $bulan, $tahun);
                    InventoryPcComponent::updateOrCreate(
                        ['inventory_id' => $inventory->id, 'komponen' => $komponen],
                        [$config['fk'] => $hwId, 'kondisi' => 'Baik', 'urutan' => $urutan++]
                    );
                }

                $successCount++;
            });
        }

        $this->command->info("Seeding Inventaris PC dari Excel selesai. Total {$successCount} PC berhasil diimport.");
    }

    private function getHardwareId($model, $value, $bulan, $tahun)
    {
        if (!$value || $value === '-' || $value === '') return null;

        $parts = explode(' ', $value, 2);
        $merk = $parts[0];
        $tipe = $parts[1] ?? '-';

        $data = ['merk' => $merk, 'tipe' => $tipe, 'bulan' => $bulan, 'tahun' => $tahun];

        if (in_array($model, [\App\Models\RAM::class, \App\Models\Penyimpanan::class, \App\Models\VGA::class])) {
            preg_match('/(\d+)/', $value, $matches);
            $data['kapasitas'] = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        if ($model === \App\Models\Monitor::class) {
            unset($data['tipe']);
            $data['nama'] = $tipe;
            $data['ukuran'] = preg_match('/(\d+)/', $tipe, $matches) ? $matches[1] : '0';
        }

        if ($model === \App\Models\DVD::class) {
            unset($data['tipe']);
            $data['dvd'] = $tipe;
        }

        $hw = $model::firstOrCreate($data, ['stok' => 0]);
        return $hw->id;
    }
}
