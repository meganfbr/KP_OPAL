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

        $superAdmin = User::firstOrCreate(
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

        foreach ($labs as $labSlug) {
            $labRole = 'Laboran_' . strtoupper($labSlug);

            Role::firstOrCreate([
                'name' => $labRole,
                'guard_name' => 'web',
            ]);

            // Pastikan permission tetap ada agar terdaftar pada sistem
            $this->getLabPermissions($labSlug);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Roles and permissions for UserSeeder have been securely initialized. No dummy users were created.');
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
