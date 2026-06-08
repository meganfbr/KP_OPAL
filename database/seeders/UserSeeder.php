<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Super Admin
        Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'name' => 'Super Administrator',
                'npp' => 'A11.2022.2022',
                'no_phone' => '081234567890',
                'password' => Hash::make('superadmin'),
                'tanggal_masuk' => '2020-01-01',
                'position' => 'Kepala Laboratorium',
            ]
        );

        $superAdmin->syncRoles(['super_admin']);

        $labs = [
            'D2A', 'D2B', 'D2C', 'D2D', 'D2E', 'D2F', 'D2G',
            'D2H', 'D2I', 'D2J', 'D2K', 'D3L', 'D3M', 'D3N'
        ];

        $shifts = ['pagi', 'siang'];

        $userRows = [
            ['Super Administrator', 'superadmin@mail.com', 'A11.2022.2022', 'super_admin', 'superadmin'],
        ];

        // Map beberapa lab ke tanggal_keluar contoh untuk demo
        $kontrakMap = [
            'D2A' => '2026-12-31', // Aktif sampai akhir tahun
            'D2B' => '2026-12-31',
            'D2C' => '2026-06-30', // Berakhir bulan Juni 2026 (bulan ini)
            'D2D' => '2026-06-15', // Berakhir bulan Juni 2026 (bulan ini)
            'D2E' => '2026-08-31', // Berakhir tahun ini
            'D2F' => '2026-09-30', // Berakhir tahun ini
            'D2G' => null,         // Tanpa tanggal keluar
            'D2H' => null,
            'D2I' => '2027-06-30', // Kontrak sampai tahun depan
            'D2J' => '2027-06-30',
            'D2K' => '2026-12-31',
            'D3L' => '2026-12-31',
            'D3M' => '2026-06-30', // Berakhir bulan ini
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

                // Tambahkan tanggal_keluar jika ada di map
                if (isset($kontrakMap[$labSlug])) {
                    $userData['tanggal_keluar'] = $kontrakMap[$labSlug];
                }

                $user = User::updateOrCreate(
                    ['email' => $email],
                    $userData
                );

                $user->syncRoles([$labRole]);
                $user->syncPermissions($this->getLabPermissions($labSlug));

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

        $this->command->info('Users seeded successfully!');
        $this->command->table(
            ['Name', 'Email', 'NPP', 'Role', 'Password'],
            $userRows
        );
    }

    protected function getLabPermissions(string $labSlug): array
    {
        $labSlug = strtolower($labSlug);
        $actions = ['view', 'manage', 'edit', 'delete'];

        $permissions = [];

        foreach ($actions as $action) {
            $permissions[] = "lab_{$labSlug}_{$action}";
        }

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        return $permissions;
    }
}
