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
        $this->ensureMasterHardwareExists();

        DB::transaction(function () {
            $gudang = $this->getLocation(['Gudang']);
            $labA = $this->getLocation(['LAB D2A', 'D2A', 'Lab A', 'LAB A']);
            $labB = $this->getLocation(['LAB D2B', 'D2B', 'Lab B', 'LAB B']);
            $labC = $this->getLocation(['LAB D2C', 'D2C', 'Lab C', 'LAB C']);

            $this->createPc([
                'kode_unique' => '001',
                'no_pc' => 'GD01',
                'lokasi' => $gudang,
                'asal' => $gudang,
                'posisi' => 'Client',
                'components' => [
                    'motherboard' => Motherboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'ASUS')->first(),
                    'processor' => Processor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Intel')->first(),
                    'penyimpanan' => Penyimpanan::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Seagate')->first(),
                    'vga' => VGA::where('bulan', 5)->where('tahun', 2026)->where('merk', 'NVIDIA')->first(),
                    'ram' => RAM::where('bulan', 5)->where('tahun', 2026)->where('merk', 'V-Gen')->first(),
                    'dvd' => DVD::where('bulan', 5)->where('tahun', 2026)->where('merk', 'ASUS')->first(),
                    'keyboard' => Keyboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Logitech')->first(),
                    'mouse' => Mouse::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Logitech')->first(),
                    'monitor' => Monitor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Acer')->first(),
                ],
            ]);

            $this->createPc([
                'kode_unique' => '002',
                'no_pc' => 'A01',
                'lokasi' => $labA,
                'asal' => $labA,
                'posisi' => 'Client',
                'components' => [
                    'motherboard' => Motherboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Gigabyte')->first(),
                    'processor' => Processor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'AMD')->first(),
                    'penyimpanan' => Penyimpanan::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Western Digital')->first(),
                    'vga' => VGA::where('bulan', 5)->where('tahun', 2026)->where('merk', 'AMD')->first(),
                    'ram' => RAM::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Kingston')->first(),
                    'dvd' => DVD::where('bulan', 5)->where('tahun', 2026)->where('merk', 'LG')->first(),
                    'keyboard' => Keyboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Digital Alliance')->first(),
                    'mouse' => Mouse::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Digital Alliance')->first(),
                    'monitor' => Monitor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'LG')->first(),
                ],
            ]);

            $this->createPc([
                'kode_unique' => '003',
                'no_pc' => 'B01',
                'lokasi' => $labB,
                'asal' => $labB,
                'posisi' => 'Laboran',
                'components' => [
                    'motherboard' => Motherboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'MSI')->first(),
                    'processor' => Processor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Celeron')->first(),
                    'penyimpanan' => Penyimpanan::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Kingston')->first(),
                    'vga' => VGA::where('bulan', 5)->where('tahun', 2026)->where('merk', 'ASUS')->first(),
                    'ram' => RAM::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Team Elite')->first(),
                    'dvd' => DVD::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Samsung')->first(),
                    'keyboard' => Keyboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Votre')->first(),
                    'mouse' => Mouse::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Votre')->first(),
                    'monitor' => Monitor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Samsung')->first(),
                ],
            ]);

            $this->createPc([
                'kode_unique' => '004',
                'no_pc' => 'C01',
                'lokasi' => $labC,
                'asal' => $labC,
                'posisi' => 'Dosen',
                'components' => [
                    'motherboard' => Motherboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'ASRock')->first(),
                    'processor' => Processor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Pentium')->first(),
                    'penyimpanan' => Penyimpanan::where('bulan', 5)->where('tahun', 2026)->where('merk', 'V-Gen')->first(),
                    'vga' => VGA::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Gigabyte')->first(),
                    'ram' => RAM::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Corsair')->first(),
                    'dvd' => DVD::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Pioneer')->first(),
                    'keyboard' => Keyboard::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Rexus')->first(),
                    'mouse' => Mouse::where('bulan', 5)->where('tahun', 2026)->where('merk', 'Rexus')->first(),
                    'monitor' => Monitor::where('bulan', 5)->where('tahun', 2026)->where('merk', 'AOC')->first(),
                ],
            ]);
        });

        $this->command?->info('✅ Data Inventaris PC Mei 2026 berhasil diisi.');
    }

    protected function ensureMasterHardwareExists(): void
    {
        $hasHardware = Motherboard::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && Processor::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && Penyimpanan::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && VGA::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && RAM::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && DVD::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && Keyboard::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && Mouse::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists()
            && Monitor::where('bulan', $this->bulan)->where('tahun', $this->tahun)->exists();

        if (! $hasHardware) {
            $this->call(MasterHardwareMei2026Seeder::class);
        }
    }

    protected function getLocation(array $names): Laboratorium
    {
        $location = Laboratorium::query()
            ->where(function ($query) use ($names) {
                foreach ($names as $name) {
                    $query->orWhere('ruang', $name);
                }
            })
            ->first();

        if ($location) {
            return $location;
        }

        $fallbackName = $names[0];

        $kategori = KlasifikasiLab::firstOrCreate(
            [
                'nama' => strtolower($fallbackName) === 'gudang'
                    ? 'Gudang Inventaris'
                    : 'Laboratorium Komputer',
            ],
            [
                'keterangan' => strtolower($fallbackName) === 'gudang'
                    ? 'Kategori untuk lokasi penyimpanan inventaris'
                    : 'Kategori untuk laboratorium komputer',
            ]
        );

        return Laboratorium::create([
            'kategori_id' => $kategori->id,
            'ruang' => $fallbackName,
            'kapasitas' => strtolower($fallbackName) === 'gudang' ? 999 : 40,
            'pc_siap' => 0,
            'pc_backup' => 0,
            'keterangan' => strtolower($fallbackName) === 'gudang'
                ? 'Gudang Pusat Penyimpanan Inventaris'
                : "Laboratorium {$fallbackName}",
            'is_active' => true,
        ]);
    }

    protected function createPc(array $data): void
    {
        $lokasi = $data['lokasi'];
        $asal = $data['asal'];

        $inventory = Inventory::updateOrCreate(
            [
                'kode_unique' => $data['kode_unique'],
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
            ],
            [
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

        $this->syncComponents($inventory, $data['components']);
    }

    protected function syncComponents(Inventory $inventory, array $components): void
    {
        $rows = [
            ['komponen' => 'Motherboard', 'urutan' => 1, 'motherboard_id' => $components['motherboard']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'Processor', 'urutan' => 2, 'processor_id' => $components['processor']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'Hardisk', 'urutan' => 3, 'penyimpanan_id' => $components['penyimpanan']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'VGA', 'urutan' => 4, 'vga_id' => $components['vga']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'RAM', 'urutan' => 5, 'ram_id' => $components['ram']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'DVD', 'urutan' => 6, 'dvd_id' => $components['dvd']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'Keyboard', 'urutan' => 7, 'keyboard_id' => $components['keyboard']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'Mouse', 'urutan' => 8, 'mouse_id' => $components['mouse']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
            ['komponen' => 'Monitor', 'urutan' => 9, 'monitor_id' => $components['monitor']?->id, 'kondisi' => 'Baik', 'keterangan' => null],
        ];

        foreach ($rows as $row) {
            $inventory->pcComponents()->updateOrCreate(
                [
                    'komponen' => $row['komponen'],
                ],
                $row
            );
        }
    }

    protected function findPetugasId(Laboratorium $lokasi): ?int
    {
        if (strtolower($lokasi->ruang) === 'gudang') {
            return User::role('super_admin')->first()?->id
                ?? User::query()->where('email', 'superadmin@mail.com')->value('id');
        }

        return User::query()
            ->get()
            ->filter(function (User $user) use ($lokasi) {
                if ($user->hasAnyRole(['super_admin', 'admin', 'Admin', 'Super Admin'])) {
                    return false;
                }

                if (! method_exists($user, 'getAuthorizedLabIds')) {
                    return false;
                }

                return in_array($lokasi->id, $user->getAuthorizedLabIds('view'), true);
            })
            ->first()
            ?->id;
    }
}