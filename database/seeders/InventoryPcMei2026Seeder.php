<?php

namespace Database\Seeders;

use App\Models\DVD;
use App\Models\Inventory;
use App\Models\Keyboard;
use App\Models\KlasifikasiLab;
use App\Models\Laboratorium;
use App\Models\Monitor;
use App\Models\Motherboard;
use App\Models\Mouse;
use App\Models\Penyimpanan;
use App\Models\Processor;
use App\Models\RAM;
use App\Models\User;
use App\Models\VGA;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryPcMei2026Seeder extends Seeder
{
    protected int $bulan = 5;

    protected int $tahun = 2026;

    public function run(): void
    {
        $this->call(HardwareMei2026CleanSeeder::class);

        DB::transaction(function () {
            $gudang = $this->getLocation('Gudang');
            $labA = $this->getLocation('LAB D2A');
            $labB = $this->getLocation('LAB D2B');
            $labC = $this->getLocation('LAB D2C');
            $labD = $this->getLocation('LAB D2D');

            $pcs = [
                [
                    'pc_id' => 1,
                    'no_pc' => 'GD01',
                    'kode_unique' => null,
                    'lokasi' => $gudang,
                    'asal' => $gudang,
                    'posisi' => 'Client',
                    'hardware' => [
                        'motherboard' => ['merk' => 'ASUS', 'tipe' => 'H61M-K'],
                        'processor' => ['merk' => 'Intel', 'tipe' => 'Core i3-4130'],
                        'penyimpanan' => ['merk' => 'Seagate', 'tipe' => 'HDD', 'kapasitas' => 500],
                        'vga' => ['merk' => 'NVIDIA', 'tipe' => 'GeForce GT 730', 'kapasitas' => 2],
                        'ram' => ['merk' => 'V-Gen', 'tipe' => 'DDR3', 'kapasitas' => 4],
                        'dvd' => ['merk' => 'ASUS', 'dvd' => 'DVD-RW'],
                        'keyboard' => ['merk' => 'Logitech', 'tipe' => 'K120'],
                        'mouse' => ['merk' => 'Logitech', 'tipe' => 'B100'],
                        'monitor' => ['merk' => 'Acer', 'nama' => 'V206HQL'],
                    ],
                ],
                [
                    'pc_id' => 2,
                    'no_pc' => 'GD02',
                    'kode_unique' => null,
                    'lokasi' => $gudang,
                    'asal' => $gudang,
                    'posisi' => 'Client',
                    'hardware' => [
                        'motherboard' => ['merk' => 'Gigabyte', 'tipe' => 'H81M-DS2'],
                        'processor' => ['merk' => 'AMD', 'tipe' => 'Ryzen 3 3200G'],
                        'penyimpanan' => ['merk' => 'Western Digital', 'tipe' => 'HDD', 'kapasitas' => 1000],
                        'vga' => ['merk' => 'AMD', 'tipe' => 'Radeon R5 230', 'kapasitas' => 2],
                        'ram' => ['merk' => 'Kingston', 'tipe' => 'DDR3', 'kapasitas' => 8],
                        'dvd' => ['merk' => 'LG', 'dvd' => 'DVD-RW'],
                        'keyboard' => ['merk' => 'Digital Alliance', 'tipe' => 'K1 Office'],
                        'mouse' => ['merk' => 'Digital Alliance', 'tipe' => 'M1 Office'],
                        'monitor' => ['merk' => 'LG', 'nama' => '19M38A'],
                    ],
                ],
                [
                    'pc_id' => 3,
                    'no_pc' => 'A01',
                    'kode_unique' => null,
                    'lokasi' => $labA,
                    'asal' => $labA,
                    'posisi' => 'Client',
                    'hardware' => [
                        'motherboard' => ['merk' => 'MSI', 'tipe' => 'B450M Pro-VDH'],
                        'processor' => ['merk' => 'Celeron', 'tipe' => 'G5905'],
                        'penyimpanan' => ['merk' => 'Kingston', 'tipe' => 'SSD', 'kapasitas' => 240],
                        'vga' => ['merk' => 'ASUS', 'tipe' => 'GT 1030 Silent', 'kapasitas' => 2],
                        'ram' => ['merk' => 'Team Elite', 'tipe' => 'DDR4', 'kapasitas' => 8],
                        'dvd' => ['merk' => 'Samsung', 'dvd' => 'DVD-ROM'],
                        'keyboard' => ['merk' => 'Votre', 'tipe' => 'KB230 USB'],
                        'mouse' => ['merk' => 'Votre', 'tipe' => 'MS100 USB'],
                        'monitor' => ['merk' => 'Samsung', 'nama' => 'S19F350'],
                    ],
                ],
                [
                    'pc_id' => 4,
                    'no_pc' => 'A02',
                    'kode_unique' => null,
                    'lokasi' => $labA,
                    'asal' => $labA,
                    'posisi' => 'Client',
                    'hardware' => [
                        'motherboard' => ['merk' => 'ASRock', 'tipe' => 'H510M-HDV'],
                        'processor' => ['merk' => 'Pentium', 'tipe' => 'Gold G6400'],
                        'penyimpanan' => ['merk' => 'V-Gen', 'tipe' => 'SSD', 'kapasitas' => 256],
                        'vga' => ['merk' => 'Gigabyte', 'tipe' => 'GTX 750 Ti', 'kapasitas' => 2],
                        'ram' => ['merk' => 'Corsair', 'tipe' => 'DDR4', 'kapasitas' => 16],
                        'dvd' => ['merk' => 'Pioneer', 'dvd' => 'DVD-RW'],
                        'keyboard' => ['merk' => 'Rexus', 'tipe' => 'K9 Office'],
                        'mouse' => ['merk' => 'Rexus', 'tipe' => 'Xierra S5'],
                        'monitor' => ['merk' => 'AOC', 'nama' => 'E970SWN'],
                    ],
                ],
                [
                    'pc_id' => 5,
                    'no_pc' => 'B01',
                    'kode_unique' => null,
                    'lokasi' => $labB,
                    'asal' => $labB,
                    'posisi' => 'Laboran',
                    'hardware' => [
                        'motherboard' => ['merk' => 'ASUS', 'tipe' => 'H61M-K'],
                        'processor' => ['merk' => 'AMD', 'tipe' => 'Ryzen 3 3200G'],
                        'penyimpanan' => ['merk' => 'Kingston', 'tipe' => 'SSD', 'kapasitas' => 240],
                        'vga' => ['merk' => 'NVIDIA', 'tipe' => 'GeForce GT 730', 'kapasitas' => 2],
                        'ram' => ['merk' => 'Kingston', 'tipe' => 'DDR3', 'kapasitas' => 8],
                        'dvd' => ['merk' => 'LG', 'dvd' => 'DVD-RW'],
                        'keyboard' => ['merk' => 'Logitech', 'tipe' => 'K120'],
                        'mouse' => ['merk' => 'Votre', 'tipe' => 'MS100 USB'],
                        'monitor' => ['merk' => 'Samsung', 'nama' => 'S19F350'],
                    ],
                ],
                [
                    'pc_id' => 6,
                    'no_pc' => 'B02',
                    'kode_unique' => null,
                    'lokasi' => $labB,
                    'asal' => $labB,
                    'posisi' => 'Client',
                    'hardware' => [
                        'motherboard' => ['merk' => 'Gigabyte', 'tipe' => 'H81M-DS2'],
                        'processor' => ['merk' => 'Intel', 'tipe' => 'Core i3-4130'],
                        'penyimpanan' => ['merk' => 'Seagate', 'tipe' => 'HDD', 'kapasitas' => 500],
                        'vga' => ['merk' => 'AMD', 'tipe' => 'Radeon R5 230', 'kapasitas' => 2],
                        'ram' => ['merk' => 'V-Gen', 'tipe' => 'DDR3', 'kapasitas' => 4],
                        'dvd' => ['merk' => 'ASUS', 'dvd' => 'DVD-RW'],
                        'keyboard' => ['merk' => 'Digital Alliance', 'tipe' => 'K1 Office'],
                        'mouse' => ['merk' => 'Rexus', 'tipe' => 'Xierra S5'],
                        'monitor' => ['merk' => 'LG', 'nama' => '19M38A'],
                    ],
                ],
                [
                    'pc_id' => 7,
                    'no_pc' => 'C01',
                    'kode_unique' => null,
                    'lokasi' => $labC,
                    'asal' => $labC,
                    'posisi' => 'Dosen',
                    'hardware' => [
                        'motherboard' => ['merk' => 'MSI', 'tipe' => 'B450M Pro-VDH'],
                        'processor' => ['merk' => 'Pentium', 'tipe' => 'Gold G6400'],
                        'penyimpanan' => ['merk' => 'Western Digital', 'tipe' => 'HDD', 'kapasitas' => 1000],
                        'vga' => ['merk' => 'ASUS', 'tipe' => 'GT 1030 Silent', 'kapasitas' => 2],
                        'ram' => ['merk' => 'Corsair', 'tipe' => 'DDR4', 'kapasitas' => 16],
                        'dvd' => ['merk' => 'Samsung', 'dvd' => 'DVD-ROM'],
                        'keyboard' => ['merk' => 'Votre', 'tipe' => 'KB230 USB'],
                        'mouse' => ['merk' => 'Logitech', 'tipe' => 'B100'],
                        'monitor' => ['merk' => 'Acer', 'nama' => 'V206HQL'],
                    ],
                ],
                [
                    'pc_id' => 8,
                    'no_pc' => 'D01',
                    'kode_unique' => null,
                    'lokasi' => $labD,
                    'asal' => $labD,
                    'posisi' => 'Client',
                    'hardware' => [
                        'motherboard' => ['merk' => 'ASRock', 'tipe' => 'H510M-HDV'],
                        'processor' => ['merk' => 'Celeron', 'tipe' => 'G5905'],
                        'penyimpanan' => ['merk' => 'V-Gen', 'tipe' => 'SSD', 'kapasitas' => 256],
                        'vga' => ['merk' => 'Gigabyte', 'tipe' => 'GTX 750 Ti', 'kapasitas' => 2],
                        'ram' => ['merk' => 'Team Elite', 'tipe' => 'DDR4', 'kapasitas' => 8],
                        'dvd' => ['merk' => 'Pioneer', 'dvd' => 'DVD-RW'],
                        'keyboard' => ['merk' => 'Rexus', 'tipe' => 'K9 Office'],
                        'mouse' => ['merk' => 'Digital Alliance', 'tipe' => 'M1 Office'],
                        'monitor' => ['merk' => 'AOC', 'nama' => 'E970SWN'],
                    ],
                ],
            ];

            foreach ($pcs as $pc) {
                $this->createPc($pc);
            }
        });

        $this->command?->info('Data Inventaris PC Mei 2026 berhasil dibuat.');
    }

    protected function createPc(array $data): void
    {
        $lokasi = $data['lokasi'];
        $asal = $data['asal'];

        $inventory = Inventory::updateOrCreate(
            [
                'pc_id' => $data['pc_id'],
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
            ],
            [
                'kode_unique' => $data['kode_unique'] ?? null,
                'no_pc' => $data['no_pc'],
                'nama_barang' => 'PC ' . $data['no_pc'],
                'kondisi' => 'Baik',
                'status' => 'Aktif',
                'laboratorium_id' => $lokasi->id,
                'lokasi_id' => $lokasi->id,
                'asal_id' => $asal->id,
                'petugas_id' => $this->findPetugasId($lokasi),
                'kode_inventaris' => null,
                'tanggal_pengadaan' => '2026-05-01',
                'inventoriable_id' => null,
                'inventoriable_type' => null,
            ]
        );

        $inventory->pcDetail()->updateOrCreate(
            ['inventory_id' => $inventory->id],
            ['posisi' => $data['posisi']]
        );

        $this->syncComponents($inventory, $data['hardware']);
    }

    protected function syncComponents(Inventory $inventory, array $hardware): void
    {
        $rows = [
            [
                'komponen' => 'Motherboard',
                'urutan' => 1,
                'motherboard_id' => $this->motherboard($hardware['motherboard'])->id,
            ],
            [
                'komponen' => 'Processor',
                'urutan' => 2,
                'processor_id' => $this->processor($hardware['processor'])->id,
            ],
            [
                'komponen' => 'Hardisk',
                'urutan' => 3,
                'penyimpanan_id' => $this->penyimpanan($hardware['penyimpanan'])->id,
            ],
            [
                'komponen' => 'VGA',
                'urutan' => 4,
                'vga_id' => $this->vga($hardware['vga'])->id,
            ],
            [
                'komponen' => 'RAM',
                'urutan' => 5,
                'ram_id' => $this->ram($hardware['ram'])->id,
            ],
            [
                'komponen' => 'DVD',
                'urutan' => 6,
                'dvd_id' => $this->dvd($hardware['dvd'])->id,
            ],
            [
                'komponen' => 'Keyboard',
                'urutan' => 7,
                'keyboard_id' => $this->keyboard($hardware['keyboard'])->id,
            ],
            [
                'komponen' => 'Mouse',
                'urutan' => 8,
                'mouse_id' => $this->mouse($hardware['mouse'])->id,
            ],
            [
                'komponen' => 'Monitor',
                'urutan' => 9,
                'monitor_id' => $this->monitor($hardware['monitor'])->id,
            ],
        ];

        foreach ($rows as $row) {
            $row = array_merge($row, [
                'inventory_id' => $inventory->id,
                'kondisi' => 'Baik',
                'keterangan' => null,
            ]);

            $inventory->pcComponents()->updateOrCreate(
                ['komponen' => $row['komponen']],
                $row
            );
        }
    }

    protected function motherboard(array $data): Motherboard
    {
        return Motherboard::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
            ]
        );
    }

    protected function processor(array $data): Processor
    {
        return Processor::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
            ]
        );
    }

    protected function penyimpanan(array $data): Penyimpanan
    {
        return Penyimpanan::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
                'kapasitas' => $data['kapasitas'],
            ]
        );
    }

    protected function vga(array $data): VGA
    {
        return VGA::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
                'kapasitas' => $data['kapasitas'],
            ]
        );
    }

    protected function ram(array $data): RAM
    {
        return RAM::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
                'kapasitas' => $data['kapasitas'],
            ]
        );
    }

    protected function dvd(array $data): DVD
    {
        return DVD::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'dvd' => $data['dvd'],
            ]
        );
    }

    protected function keyboard(array $data): Keyboard
    {
        return Keyboard::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
            ]
        );
    }

    protected function mouse(array $data): Mouse
    {
        return Mouse::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'tipe' => $data['tipe'],
            ]
        );
    }

    protected function monitor(array $data): Monitor
    {
        return Monitor::firstOrCreate(
            [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'merk' => $data['merk'],
                'nama' => $data['nama'],
            ]
        );
    }

    protected function getLocation(string $ruang): Laboratorium
    {
        $location = Laboratorium::where('ruang', $ruang)->first();

        if ($location) {
            return $location;
        }

        $kategori = KlasifikasiLab::firstOrCreate(
            [
                'kode_kategori' => $ruang === 'Gudang' ? 'K99' : 'K01',
            ],
            [
                'nama_kategori' => $ruang === 'Gudang' ? 'Gudang Inventaris' : 'Laboratorium Komputer',
            ]
        );

        return Laboratorium::create([
            'kategori_id' => $kategori->id,
            'ruang' => $ruang,
            'kapasitas' => $ruang === 'Gudang' ? 999 : 40,
            'pc_siap' => $ruang === 'Gudang' ? 0 : 40,
            'pc_backup' => $ruang === 'Gudang' ? 0 : 2,
            'keterangan' => $ruang === 'Gudang'
                ? 'Gudang Pusat Penyimpanan Inventaris'
                : 'Laboratorium Komputer',
            'is_active' => true,
        ]);
    }

    protected function findPetugasId(Laboratorium $lokasi): ?int
    {
        if (str_contains(strtolower($lokasi->ruang), 'gudang')) {
            return null;
        }

        return User::query()
            ->get()
            ->first(fn (User $user): bool => method_exists($user, 'getAuthorizedLabIds')
                && ! $user->hasAnyRole(['super_admin', 'admin', 'Admin', 'Super Admin'])
                && in_array($lokasi->id, $user->getAuthorizedLabIds('view'), true))
            ?->id;
    }
}