<?php

namespace Database\Seeders;

use App\Models\DVD;
use App\Models\Keyboard;
use App\Models\KlasifikasiLab;
use App\Models\Laboratorium;
use App\Models\Monitor;
use App\Models\Motherboard;
use App\Models\Mouse;
use App\Models\Penyimpanan;
use App\Models\Processor;
use App\Models\RAM;
use App\Models\VGA;
use Illuminate\Database\Seeder;

class HardwarePcSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = KlasifikasiLab::firstOrCreate(
            ['kode_kategori' => 'LAB'],
            ['nama_kategori' => 'Laboratorium']
        );

        Laboratorium::firstOrCreate(
            ['ruang' => 'Gudang'],
            [
                'kategori_id' => $kategori->id,
                'kapasitas' => 0,
                'keterangan' => 'Lokasi penyimpanan PC',
            ]
        );

        foreach (range('A', 'N') as $kodeLab) {
            Laboratorium::firstOrCreate(
                ['ruang' => 'Lab ' . $kodeLab],
                [
                    'kategori_id' => $kategori->id,
                    'kapasitas' => 30,
                ]
            );
        }

        foreach (['ASUS H61M', 'Gigabyte H81M', 'MSI B450M'] as $merk) {
            Motherboard::firstOrCreate(['merk' => $merk]);
        }

        foreach (['Intel Core i3', 'Intel Core i5', 'AMD Ryzen 3'] as $merk) {
            Processor::firstOrCreate(['merk' => $merk]);
        }

        foreach (['HDD Seagate 500GB', 'SSD Kingston 256GB', 'SSD Samsung 512GB'] as $merk) {
            Penyimpanan::firstOrCreate(['merk' => $merk]);
        }

        foreach (['NVIDIA GT 730', 'NVIDIA GTX 1050', 'Intel HD Graphics'] as $merk) {
            VGA::firstOrCreate(['merk' => $merk]);
        }

        foreach (['Kingston 4GB', 'Kingston 8GB', 'V-Gen 8GB'] as $merk) {
            RAM::firstOrCreate(['merk' => $merk]);
        }

        foreach (['LG DVD-RW', 'Samsung DVD-RW', 'Asus DVD-RW'] as $merk) {
            DVD::firstOrCreate(['merk' => $merk]);
        }

        foreach (['Logitech Keyboard', 'Votre Keyboard', 'Digital Alliance Keyboard'] as $merk) {
            Keyboard::firstOrCreate(['merk' => $merk]);
        }

        foreach (['Logitech Mouse', 'Votre Mouse', 'Digital Alliance Mouse'] as $merk) {
            Mouse::firstOrCreate(['merk' => $merk]);
        }

        foreach (['LG 19 Inch', 'Samsung 19 Inch', 'Acer 20 Inch'] as $merk) {
            Monitor::firstOrCreate(['merk' => $merk]);
        }
    }
}