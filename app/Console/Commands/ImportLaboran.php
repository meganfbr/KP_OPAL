<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ImportLaboran extends Command
{
    protected $signature = 'app:import-laboran';
    protected $description = 'Import Laboran from JSON exported from Excel';

    public function handle()
    {
        $filePath = base_path('laboran_import_data.json');
        if (!File::exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }

        $data = json_decode(File::get($filePath), true);
        $this->info("Importing " . count($data) . " users...");

        foreach ($data as $item) {
            $user = User::updateOrCreate(
                ['npp' => $item['npp']],
                [
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'password' => Hash::make('password123'),
                    'tanggal_masuk' => $this->parseDate($item['tanggal_masuk']),
                    'tanggal_keluar' => $this->parseDate($item['tanggal_keluar']),
                    'is_active' => $item['is_active'],
                ]
            );

            // Sync Roles
            if (!empty($item['roles'])) {
                $user->syncRoles($item['roles']);
            }

            // Update Petugas ID on Inventories
            foreach ($item['roles'] as $roleName) {
                if (str_starts_with($roleName, 'Laboran_')) {
                    $labRuang = 'LAB ' . str_replace('Laboran_', '', $roleName);
                    $lab = Laboratorium::where('ruang', $labRuang)->first();
                    
                    if ($lab) {
                        Inventory::where('laboratorium_id', $lab->id)
                            ->update(['petugas_id' => $user->id]);
                        
                        $this->line("Assigned {$user->name} as petugas for {$labRuang}");
                    }
                }
            }
        }

        $this->info("Import completed!");
    }

    private function parseDate($dateStr)
    {
        if (!$dateStr || $dateStr === 'nan') return null;
        try {
            // Excel dates are often D/M/Y or Y-M-D
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
