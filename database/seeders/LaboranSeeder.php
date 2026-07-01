<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class LaboranSeeder extends Seeder
{
    public function run(): void
    {
        $excelPath = database_path('imports/Data_Laboran_2026-06-30.xlsx');
        
        if (!file_exists($excelPath)) {
            $this->command->warn("File Excel laboran tidak ditemukan di: {$excelPath}. Import dilewati.");
            return;
        }

        $this->command->info("Mengkonversi data laboran dari Excel menggunakan Python (ZipArchive fallback)...");

        $pythonScript = '
import pandas as pd
import json
import sys

try:
    df = pd.read_excel(r"' . addslashes($excelPath) . '")
    data = []
    for _, row in df.iterrows():
        npp = str(row["NPP/NIM"]).strip() if pd.notna(row["NPP/NIM"]) else ""
        if not npp or npp == "nan": continue
        
        email = str(row["Email"]).strip() if pd.notna(row["Email"]) else ""
        if not email or email == "-" or email.lower() == "nan":
            email = f"{npp.replace(\'.\', \'\')}@siopal.local"
            
        # Perbaiki format number scientific untuk no HP
        no_hp = str(row["No HP"]).strip() if pd.notna(row["No HP"]) else ""
        if no_hp.lower() == "nan": no_hp = ""
        else: no_hp = no_hp.split(".")[0] # remove .0 if any
        
        roles_str = str(row["Role"]).strip() if pd.notna(row["Role"]) else ""
        if roles_str.lower() == "nan": roles_str = ""
        roles = [r.strip() for r in roles_str.split(",") if r.strip()]
        
        position = "Laboran"
        if "super_admin" in roles_str.lower():
            position = "Kepala Laboratorium"

        t_masuk = str(row["Tanggal Masuk"]).strip() if pd.notna(row["Tanggal Masuk"]) else ""
        t_keluar = str(row["Tanggal Keluar"]).strip() if pd.notna(row["Tanggal Keluar"]) else ""
        
        status_val = str(row["Status"]).strip().lower() if pd.notna(row["Status"]) else ""
        is_active = True if status_val == "aktif" else False

        data.append({
            "name": str(row["Nama"]).strip() if pd.notna(row["Nama"]) and str(row["Nama"]) != "nan" else "",
            "npp": npp,
            "email": email,
            "no_phone": no_hp,
            "position": position,
            "roles": roles,
            "tanggal_masuk": t_masuk if t_masuk != "-" and t_masuk != "nan" else None,
            "tanggal_keluar": t_keluar if t_keluar != "-" and t_keluar != "nan" else None,
            "is_active": is_active,
        })
    print(json.dumps(data))
except Exception as e:
    sys.stderr.write(str(e))
    sys.exit(1)
';

        $tempPy = tempnam(sys_get_temp_dir(), 'import_laboran');
        file_put_contents($tempPy, $pythonScript);
        
        $jsonOutput = shell_exec("python " . escapeshellarg($tempPy));
        unlink($tempPy);

        if (!$jsonOutput) {
            $this->command->error("Gagal mengkonversi Excel menggunakan Python. Pastikan pandas & openpyxl terinstall.");
            return;
        }

        $data = json_decode(trim($jsonOutput), true);
        if (!$data || !is_array($data)) {
            $this->command->error("Format data hasil konversi tidak valid: " . substr($jsonOutput, 0, 100));
            return;
        }

        $this->command->info("Memproses " . count($data) . " user laboran...");

        foreach ($data as $item) {
            $user = User::where('npp', $item['npp'])->first();
            if (!$user) {
                $user = new User([
                    'npp' => $item['npp'],
                    'password' => Hash::make('password123'),
                ]);
            }

            $user->name = $item['name'];
            $user->email = $item['email'];
            $user->no_phone = rtrim((string)$item['no_phone'], '.0');
            $user->position = $item['position'];
            $user->tanggal_masuk = $this->parseDate($item['tanggal_masuk']);
            $user->tanggal_keluar = $this->parseDate($item['tanggal_keluar']);
            // Untuk memastikan is_active diset dengan benar (boolean)
            $user->is_active = filter_var($item['is_active'], FILTER_VALIDATE_BOOLEAN);
            $user->save();

            if (!empty($item['roles'])) {
                $validRoles = [];
                foreach ($item['roles'] as $r) {
                    Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
                    $validRoles[] = $r;
                }
                $user->syncRoles($validRoles);
            }
        }

        $labs = Laboratorium::all();
        foreach ($labs as $lab) {
            $roleName = 'Laboran_' . str_replace('LAB ', '', $lab->ruang);
            try {
                $petugas = User::role($roleName)->orderBy('id')->first();
            
                if ($petugas) {
                    Inventory::where('laboratorium_id', $lab->id)
                        ->whereNull('inventoriable_type')
                        ->update(['petugas_id' => $petugas->id]);
                }
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                // Ignore
            }
        }

        $this->command->info("Import laboran selesai.");
    }

    private function parseDate($dateStr)
    {
        if (empty($dateStr)) return null;
        
        try {
            return Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($dateStr)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}
