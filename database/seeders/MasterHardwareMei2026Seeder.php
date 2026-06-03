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
use Illuminate\Database\Seeder;

class MasterHardwareMei2026Seeder extends Seeder
{
    public function run(): void
    {
        $bulan = 5;
        $tahun = 2026;

        $this->seedMotherboards($bulan, $tahun);
        $this->seedProcessors($bulan, $tahun);
        $this->seedPenyimpanans($bulan, $tahun);
        $this->seedVgas($bulan, $tahun);
        $this->seedRams($bulan, $tahun);
        $this->seedDvds($bulan, $tahun);
        $this->seedKeyboards($bulan, $tahun);
        $this->seedMice($bulan, $tahun);
        $this->seedMonitors($bulan, $tahun);

        $this->command?->info('✅ Master data hardware Mei 2026 berhasil diisi.');
    }

    protected function seedMotherboards(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'ASUS', 'tipe' => 'H61M-K'],
            ['merk' => 'Gigabyte', 'tipe' => 'H81M-DS2'],
            ['merk' => 'MSI', 'tipe' => 'B450M Pro-VDH'],
            ['merk' => 'ASRock', 'tipe' => 'H510M-HDV'],
        ];

        foreach ($rows as $row) {
            Motherboard::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedProcessors(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'Intel', 'tipe' => 'Core i3-4130'],
            ['merk' => 'AMD', 'tipe' => 'Ryzen 3 3200G'],
            ['merk' => 'Celeron', 'tipe' => 'G5905'],
            ['merk' => 'Pentium', 'tipe' => 'Gold G6400'],
        ];

        foreach ($rows as $row) {
            Processor::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedPenyimpanans(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'Seagate', 'tipe' => 'HDD', 'kapasitas' => 500, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Western Digital', 'tipe' => 'HDD', 'kapasitas' => 1000, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Kingston', 'tipe' => 'SSD', 'kapasitas' => 240, 'spesifikasi' => 'SATA 2.5 Inch'],
            ['merk' => 'V-Gen', 'tipe' => 'SSD', 'kapasitas' => 256, 'spesifikasi' => 'SATA 2.5 Inch'],
        ];

        foreach ($rows as $row) {
            Penyimpanan::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'kapasitas' => $row['kapasitas'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedVgas(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'NVIDIA', 'tipe' => 'GeForce GT 730', 'kapasitas' => 2, 'spesifikasi' => 'DDR3'],
            ['merk' => 'AMD', 'tipe' => 'Radeon R5 230', 'kapasitas' => 2, 'spesifikasi' => 'DDR3'],
            ['merk' => 'ASUS', 'tipe' => 'GT 1030 Silent', 'kapasitas' => 2, 'spesifikasi' => 'GDDR5'],
            ['merk' => 'Gigabyte', 'tipe' => 'GTX 750 Ti', 'kapasitas' => 2, 'spesifikasi' => 'GDDR5'],
        ];

        foreach ($rows as $row) {
            VGA::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'kapasitas' => $row['kapasitas'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedRams(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'V-Gen', 'tipe' => 'DDR3', 'kapasitas' => 4],
            ['merk' => 'Kingston', 'tipe' => 'DDR3', 'kapasitas' => 8],
            ['merk' => 'Team Elite', 'tipe' => 'DDR4', 'kapasitas' => 8],
            ['merk' => 'Corsair', 'tipe' => 'DDR4', 'kapasitas' => 16],
        ];

        foreach ($rows as $row) {
            RAM::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'kapasitas' => $row['kapasitas'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedDvds(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'ASUS', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'LG', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'Samsung', 'dvd' => 'DVD-ROM', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'Pioneer', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
        ];

        foreach ($rows as $row) {
            DVD::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'dvd' => $row['dvd'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedKeyboards(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'Logitech', 'tipe' => 'K120'],
            ['merk' => 'Digital Alliance', 'tipe' => 'K1 Office'],
            ['merk' => 'Votre', 'tipe' => 'KB230 USB'],
            ['merk' => 'Rexus', 'tipe' => 'K9 Office'],
        ];

        foreach ($rows as $row) {
            Keyboard::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedMice(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'Logitech', 'tipe' => 'B100'],
            ['merk' => 'Digital Alliance', 'tipe' => 'M1 Office'],
            ['merk' => 'Votre', 'tipe' => 'MS100 USB'],
            ['merk' => 'Rexus', 'tipe' => 'Xierra S5'],
        ];

        foreach ($rows as $row) {
            Mouse::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'tipe' => $row['tipe'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'stok' => 0,
                ]
            );
        }
    }

    protected function seedMonitors(int $bulan, int $tahun): void
    {
        $rows = [
            ['merk' => 'Acer', 'nama' => 'V206HQL', 'resolusi' => '1366x768', 'ukuran' => '20', 'spesifikasi' => 'LED Monitor'],
            ['merk' => 'LG', 'nama' => '19M38A', 'resolusi' => '1366x768', 'ukuran' => '19', 'spesifikasi' => 'LED Monitor'],
            ['merk' => 'Samsung', 'nama' => 'S19F350', 'resolusi' => '1366x768', 'ukuran' => '19', 'spesifikasi' => 'LED Monitor'],
            ['merk' => 'AOC', 'nama' => 'E970SWN', 'resolusi' => '1366x768', 'ukuran' => '18.5', 'spesifikasi' => 'LED Monitor'],
        ];

        foreach ($rows as $row) {
            Monitor::updateOrCreate(
                [
                    'merk' => $row['merk'],
                    'nama' => $row['nama'],
                    'ukuran' => $row['ukuran'],
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'resolusi' => $row['resolusi'],
                    'spesifikasi' => $row['spesifikasi'],
                    'stok' => 0,
                ]
            );
        }
    }
}