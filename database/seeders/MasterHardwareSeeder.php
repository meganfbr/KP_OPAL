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

class MasterHardwareSeeder extends Seeder
{
    public function run(): void
    {
        $bulan = (int) now()->month;
        $tahun = (int) now()->year;

        /*
         * Motherboard
         */
        $motherboards = [
            ['merk' => 'ASUS', 'tipe' => 'H61M-K'],
            ['merk' => 'ASUS', 'tipe' => 'H81M-D'],
            ['merk' => 'Gigabyte', 'tipe' => 'H61M-DS2'],
            ['merk' => 'MSI', 'tipe' => 'H81M-P33'],
        ];

        foreach ($motherboards as $row) {
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

        /*
         * Processor
         */
        $processors = [
            ['merk' => 'Intel', 'tipe' => 'Core i3-4130'],
            ['merk' => 'Intel', 'tipe' => 'Core i5-4570'],
            ['merk' => 'Intel', 'tipe' => 'Core i5-6500'],
            ['merk' => 'AMD', 'tipe' => 'Ryzen 3 3200G'],
        ];

        foreach ($processors as $row) {
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

        /*
         * Penyimpanan / Hardisk
         */
        $penyimpanans = [
            ['merk' => 'Seagate', 'tipe' => 'HDD', 'kapasitas' => 500, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Seagate', 'tipe' => 'HDD', 'kapasitas' => 1000, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Western Digital', 'tipe' => 'HDD', 'kapasitas' => 500, 'spesifikasi' => 'SATA 3.5 Inch'],
            ['merk' => 'Kingston', 'tipe' => 'SSD', 'kapasitas' => 240, 'spesifikasi' => 'SATA 2.5 Inch'],
            ['merk' => 'V-Gen', 'tipe' => 'SSD', 'kapasitas' => 256, 'spesifikasi' => 'SATA 2.5 Inch'],
        ];

        foreach ($penyimpanans as $row) {
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

        /*
         * VGA
         */
        $vgas = [
            ['merk' => 'NVIDIA', 'tipe' => 'GeForce GT 730', 'kapasitas' => 2, 'spesifikasi' => 'DDR3'],
            ['merk' => 'NVIDIA', 'tipe' => 'GeForce GT 1030', 'kapasitas' => 2, 'spesifikasi' => 'GDDR5'],
            ['merk' => 'AMD', 'tipe' => 'Radeon R5 230', 'kapasitas' => 2, 'spesifikasi' => 'DDR3'],
        ];

        foreach ($vgas as $row) {
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

        /*
         * RAM
         */
        $rams = [
            ['merk' => 'V-Gen', 'tipe' => 'DDR3', 'kapasitas' => 4],
            ['merk' => 'V-Gen', 'tipe' => 'DDR3', 'kapasitas' => 8],
            ['merk' => 'Kingston', 'tipe' => 'DDR3', 'kapasitas' => 4],
            ['merk' => 'Kingston', 'tipe' => 'DDR4', 'kapasitas' => 8],
            ['merk' => 'Team Elite', 'tipe' => 'DDR4', 'kapasitas' => 8],
        ];

        foreach ($rams as $row) {
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

        /*
         * DVD
         */
        $dvds = [
            ['merk' => 'ASUS', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'LG', 'dvd' => 'DVD-RW', 'spesifikasi' => 'Internal SATA'],
            ['merk' => 'Samsung', 'dvd' => 'DVD-ROM', 'spesifikasi' => 'Internal SATA'],
        ];

        foreach ($dvds as $row) {
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

        /*
         * Keyboard
         */
        $keyboards = [
            ['merk' => 'Logitech', 'tipe' => 'K120'],
            ['merk' => 'Digital Alliance', 'tipe' => 'Standard Keyboard'],
            ['merk' => 'Votre', 'tipe' => 'USB Keyboard'],
            ['merk' => 'Rexus', 'tipe' => 'Office Keyboard'],
        ];

        foreach ($keyboards as $row) {
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

        /*
         * Mouse
         */
        $mice = [
            ['merk' => 'Logitech', 'tipe' => 'B100'],
            ['merk' => 'Digital Alliance', 'tipe' => 'Standard Mouse'],
            ['merk' => 'Votre', 'tipe' => 'USB Mouse'],
            ['merk' => 'Rexus', 'tipe' => 'Office Mouse'],
        ];

        foreach ($mice as $row) {
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

        /*
         * Monitor
         */
        $monitors = [
            [
                'merk' => 'Acer',
                'nama' => 'V206HQL',
                'resolusi' => '1366x768',
                'ukuran' => '20',
                'spesifikasi' => 'LED Monitor',
            ],
            [
                'merk' => 'LG',
                'nama' => '19M38A',
                'resolusi' => '1366x768',
                'ukuran' => '19',
                'spesifikasi' => 'LED Monitor',
            ],
            [
                'merk' => 'Samsung',
                'nama' => 'S19F350',
                'resolusi' => '1366x768',
                'ukuran' => '19',
                'spesifikasi' => 'LED Monitor',
            ],
            [
                'merk' => 'AOC',
                'nama' => 'E970SWN',
                'resolusi' => '1366x768',
                'ukuran' => '18.5',
                'spesifikasi' => 'LED Monitor',
            ],
        ];

        foreach ($monitors as $row) {
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

        $this->command->info('✅ Master data hardware PC berhasil diisi.');
    }
}