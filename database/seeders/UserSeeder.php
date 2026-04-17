<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
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
        $superAdmin->assignRole('super_admin');

        $labUsers = [
            'D2A' => ['email' => 'laboran_labd2a@mail.com', 'password' => 'lab-d2a'],
            'D2B' => ['email' => 'laboran_labd2b@mail.com', 'password' => 'lab-d2b'],
            'D2C' => ['email' => 'laboran_labd2c@mail.com', 'password' => 'lab-d2c'],
            'D2D' => ['email' => 'laboran_labd2d@mail.com', 'password' => 'lab-d2d'],
            'D2E' => ['email' => 'laboran_labd2e@mail.com', 'password' => 'lab-d2e'],
            'D2F' => ['email' => 'laboran_labd2f@mail.com', 'password' => 'lab-d2f'],
            'D2G' => ['email' => 'laboran_labd2g@mail.com', 'password' => 'lab-d2g'],
            'D2H' => ['email' => 'laboran_labd2h@mail.com', 'password' => 'lab-d2h'],
            'D2I' => ['email' => 'laboran_labd2i@mail.com', 'password' => 'lab-d2i'],
            'D2J' => ['email' => 'laboran_labd2j@mail.com', 'password' => 'lab-d2j'],
            'D2K' => ['email' => 'laboran_labd2k@mail.com', 'password' => 'lab-d2k'],
            'D3L' => ['email' => 'laboran_labd3l@mail.com', 'password' => 'lab-d3l'],
            'D3M' => ['email' => 'laboran_labd3m@mail.com', 'password' => 'lab-d3m'],
            'D3N' => ['email' => 'laboran_labd3n@mail.com', 'password' => 'lab-d3n'],
        ];

        $userRows = [
            ['Super Administrator', 'superadmin@mail.com', 'A11.2022.2022', 'super_admin', 'superadmin'],
        ];

        foreach ($labUsers as $labSlug => $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => 'Laboran Lab ' . strtoupper($labSlug),
                    'npp' => 'LAB' . strtolower($labSlug) . '.2026',
                    'no_phone' => '081234567890',
                    'password' => Hash::make($userData['password']),
                    'tanggal_masuk' => '2024-01-01',
                    'position' => 'Laboran',
                ]
            );

            $labRole = 'Laboran_' . strtoupper($labSlug);
            $user->syncRoles([$labRole]);
            $user->givePermissionTo($this->getLabPermissions($labSlug));

            $userRows[] = [
                $user->name,
                $userData['email'],
                $user->npp,
                $labRole,
                $userData['password'],
            ];
        }

        $this->command->info('Users seeded successfully!');
        $this->command->table(
            ['Name', 'Email', 'NPP', 'Role', 'Password'],
            $userRows
        );
    }

    protected function getLabPermissions(string $labSlug): array
    {
        $labSlug = strtolower($labSlug);

        $permissions = [
            "lab_{$labSlug}_view",
            "lab_{$labSlug}_manage",
            "lab_{$labSlug}_edit",
            "lab_{$labSlug}_delete",
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        return $permissions;
    }
}
