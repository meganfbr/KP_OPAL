<?php

namespace Database\Seeders;

use App\Models\DVD;
use App\Models\Keyboard;
use App\Models\Monitor;
use App\Models\Motherboard;
use App\Models\Mouse;
use App\Models\Penyimpanan;
use App\Models\Processor;
use App\Models\RAM;
use App\Models\VGA;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HardwareMei2026CleanSeeder extends Seeder
{
    protected int $bulan = 5;

    protected int $tahun = 2026;

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedMotherboards();
            $this->seedProcessors();
            $this->seedPenyimpanans();
            $this->seedVgas();
            $this->seedRams();
            $this->seedDvds();
            $this->seedKeyboards();
            $this->seedMice();
            $this->seedMonitors();
        });

        $this->command?->info('Data hardware Mei 2026 berhasil dibuat dan data dobel sudah dirapikan.');
    }

    protected function seedMotherboards(): void
    {
        $rows = [
            ['merk' => 'ASUS', 'tipe' => 'H61M-K'],
            ['merk' => 'Gigabyte', 'tipe' => 'H81M-DS2'],
            ['merk' => 'MSI', 'tipe' => 'B450M Pro-VDH'],
            ['merk' => 'ASRock', 'tipe' => 'H510M-HDV'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: Motherboard::class,
                table: 'motherboards',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'stok' => 0,
                ],
                componentFk: 'motherboard_id'
            );
        }
    }

    protected function seedProcessors(): void
    {
        $rows = [
            ['merk' => 'Intel', 'tipe' => 'Core i3-4130'],
            ['merk' => 'AMD', 'tipe' => 'Ryzen 3 3200G'],
            ['merk' => 'Celeron', 'tipe' => 'G5905'],
            ['merk' => 'Pentium', 'tipe' => 'Gold G6400'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: Processor::class,
                table: 'processors',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'stok' => 0,
                ],
                componentFk: 'processor_id'
            );
        }
    }

    protected function seedPenyimpanans(): void
    {
        $rows = [
            ['merk' => 'Seagate', 'tipe' => 'HDD', 'kapasitas' => 500, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Western Digital', 'tipe' => 'HDD', 'kapasitas' => 1000, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Kingston', 'tipe' => 'SSD', 'kapasitas' => 240, 'spesifikasi' => 'SATA 2.5 Inch'],
            ['merk' => 'V-Gen', 'tipe' => 'SSD', 'kapasitas' => 256, 'spesifikasi' => 'SATA 2.5 Inch'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: Penyimpanan::class,
                table: 'penyimpanans',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'kapasitas' => $row['kapasitas'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ],
                componentFk: 'penyimpanan_id'
            );
        }
    }

    protected function seedVgas(): void
    {
        $rows = [
            ['merk' => 'NVIDIA', 'tipe' => 'GeForce GT 730', 'kapasitas' => 2, 'spesifikasi' => 'DDR3'],
            ['merk' => 'AMD', 'tipe' => 'Radeon R5 230', 'kapasitas' => 2, 'spesifikasi' => 'DDR3'],
            ['merk' => 'ASUS', 'tipe' => 'GT 1030 Silent', 'kapasitas' => 2, 'spesifikasi' => 'GDDR5'],
            ['merk' => 'Gigabyte', 'tipe' => 'GTX 750 Ti', 'kapasitas' => 2, 'spesifikasi' => 'GDDR5'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: VGA::class,
                table: 'v_g_a_s',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'kapasitas' => $row['kapasitas'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ],
                componentFk: 'vga_id'
            );
        }
    }

    protected function seedRams(): void
    {
        $rows = [
            ['merk' => 'V-Gen', 'tipe' => 'DDR3', 'kapasitas' => 4],
            ['merk' => 'Kingston', 'tipe' => 'DDR3', 'kapasitas' => 8],
            ['merk' => 'Team Elite', 'tipe' => 'DDR4', 'kapasitas' => 8],
            ['merk' => 'Corsair', 'tipe' => 'DDR4', 'kapasitas' => 16],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: RAM::class,
                table: 'r_a_m_s',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'kapasitas' => $row['kapasitas'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'stok' => 0,
                ],
                componentFk: 'ram_id'
            );
        }
    }

    protected function seedDvds(): void
    {
        $rows = [
            ['merk' => 'ASUS', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'LG', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'Samsung', 'dvd' => 'DVD-ROM', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'Pioneer', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: DVD::class,
                table: 'd_v_d_s',
                match: [
                    'merk' => $row['merk'],
                    'dvd' => $row['dvd'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ],
                componentFk: 'dvd_id'
            );
        }
    }

    protected function seedKeyboards(): void
    {
        $rows = [
            ['merk' => 'Logitech', 'tipe' => 'K120'],
            ['merk' => 'Digital Alliance', 'tipe' => 'K1 Office'],
            ['merk' => 'Votre', 'tipe' => 'KB230 USB'],
            ['merk' => 'Rexus', 'tipe' => 'K9 Office'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: Keyboard::class,
                table: 'keyboards',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'stok' => 0,
                ],
                componentFk: 'keyboard_id'
            );
        }
    }

    protected function seedMice(): void
    {
        $rows = [
            ['merk' => 'Logitech', 'tipe' => 'B100'],
            ['merk' => 'Digital Alliance', 'tipe' => 'M1 Office'],
            ['merk' => 'Votre', 'tipe' => 'MS100 USB'],
            ['merk' => 'Rexus', 'tipe' => 'Xierra S5'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: Mouse::class,
                table: 'mice',
                match: [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'stok' => 0,
                ],
                componentFk: 'mouse_id'
            );
        }
    }

    protected function seedMonitors(): void
    {
        $rows = [
            ['merk' => 'Acer', 'nama' => 'V206HQL', 'resolusi' => '1366x768', 'ukuran' => '20', 'spesifikasi' => 'LED Monitor'],
            ['merk' => 'LG', 'nama' => '19M38A', 'resolusi' => '1366x768', 'ukuran' => '19', 'spesifikasi' => 'LED Monitor'],
            ['merk' => 'Samsung', 'nama' => 'S19F350', 'resolusi' => '1366x768', 'ukuran' => '19', 'spesifikasi' => 'LED Monitor'],
            ['merk' => 'AOC', 'nama' => 'E970SWN', 'resolusi' => '1366x768', 'ukuran' => '18.5', 'spesifikasi' => 'LED Monitor'],
        ];

        foreach ($rows as $row) {
            $this->upsertAndClean(
                model: Monitor::class,
                table: 'monitors',
                match: [
                    'merk' => $row['merk'],
                    'nama' => $row['nama'],
                    'ukuran' => $row['ukuran'],
                    'bulan' => $this->bulan,
                    'tahun' => $this->tahun,
                ],
                update: [
                    'resolusi' => $row['resolusi'],
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ],
                componentFk: 'monitor_id'
            );
        }
    }

    protected function upsertAndClean(string $model, string $table, array $match, array $update, string $componentFk): Model
    {
        /** @var Model|null $main */
        $main = $model::query()
            ->where($match)
            ->orderBy('id')
            ->first();

        if ($main) {
            $main->fill($update);
            $main->save();
        } else {
            /** @var Model $main */
            $main = $model::create(array_merge($match, $update));
        }

        $duplicates = DB::table($table)
            ->where($match)
            ->where('id', '!=', $main->getKey())
            ->orderBy('id')
            ->pluck('id');

        if ($duplicates->isNotEmpty()) {
            DB::table('inventory_pc_components')
                ->whereIn($componentFk, $duplicates)
                ->update([
                    $componentFk => $main->getKey(),
                ]);

            DB::table($table)
                ->whereIn('id', $duplicates)
                ->delete();
        }

        return $main;
    }
}