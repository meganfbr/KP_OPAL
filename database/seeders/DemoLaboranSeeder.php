<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DemoLaboranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=DemoLaboranSeeder
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->warn('Menjalankan DemoLaboranSeeder. Ini akan membuat/menimpa dummy laboran pagi/siang.');

        $labs = [
            'D2A', 'D2B', 'D2C', 'D2D', 'D2E', 'D2F', 'D2G',
            'D2H', 'D2I', 'D2J', 'D2K', 'D3L', 'D3M', 'D3N'
        ];

        $shifts = ['pagi', 'siang'];

        $userRows = [];

        // Map beberapa lab ke tanggal_keluar contoh untuk demo
        $kontrakMap = [
            'D2A' => '2026-12-31', 
            'D2B' => '2026-12-31',
            'D2C' => '2026-06-30', 
            'D2D' => '2026-06-15', 
            'D2E' => '2026-08-31', 
            'D2F' => '2026-09-30', 
            'D2G' => null,         
            'D2H' => null,
            'D2I' => '2027-06-30', 
            'D2J' => '2027-06-30',
            'D2K' => '2026-12-31',
            'D3L' => '2026-12-31',
            'D3M' => '2026-06-30', 
            'D3N' => null,
        ];

        foreach ($labs as $labSlug) {
            $labRole = 'Laboran_' . strtoupper($labSlug);

            Role::firstOrCreate([
                'name' => $labRole,
                'guard_name' => 'web',
            ]);

            foreach ($shifts as $shift) {
                $labSlugLower = strtolower($labSlug);
                $email = "laboran_{$labSlugLower}_{$shift}@mail.com";
                $passwordPlain = "lab-{$labSlugLower}-{$shift}";
                $npp = "LAB{$labSlugLower}.{$shift}.2026";
                $name = "Laboran Lab " . strtoupper($labSlug) . " " . ucfirst($shift);

                $userData = [
                    'name' => $name,
                    'npp' => $npp,
                    'no_phone' => '081234567890',
                    'password' => Hash::make($passwordPlain),
                    'tanggal_masuk' => '2026-01-01',
                    'position' => 'Laboran',
                ];

                if (isset($kontrakMap[$labSlug])) {
                    $userData['tanggal_keluar'] = $kontrakMap[$labSlug];
                }

                $user = User::updateOrCreate(
                    ['email' => $email],
                    $userData
                );

                $user->syncRoles([$labRole]);

                // Sync Direct Permissions
                $permissions = [];
                $actions = ['view', 'manage', 'edit', 'delete'];
                foreach ($actions as $action) {
                    $permissions[] = "lab_{$labSlugLower}_{$action}";
                    
                    // Pastikan permission exist
                    Permission::firstOrCreate([
                        'name' => "lab_{$labSlugLower}_{$action}",
                        'guard_name' => 'web',
                    ]);
                }
                
                $user->syncPermissions($permissions);

                $userRows[] = [
                    $user->name,
                    $email,
                    $user->npp,
                    $labRole,
                    $passwordPlain,
                ];
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Dummy Demo Laboran users seeded successfully!');
        $this->command->table(
            ['Name', 'Email', 'NPP', 'Role', 'Password'],
            $userRows
        );
    }
}
