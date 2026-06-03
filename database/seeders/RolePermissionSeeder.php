<?php

namespace Database\Seeders;

use App\Models\Laboratorium;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $inventoryPermissions = [
            'view_any_software::inventory',
            'view_software::inventory',
            'create_software::inventory',
            'update_software::inventory',
            'delete_software::inventory',
            'delete_any_software::inventory',
            'force_delete_software::inventory',
            'force_delete_any_software::inventory',
            'restore_software::inventory',
            'restore_any_software::inventory',
            'replicate_software::inventory',
            'reorder_software::inventory',
        ];

        $navigationPermissions = [
            'view_any_schedule',
            'view_schedule',
            'view-navigation-item::motherboard',
            'view-navigation-item::processor',
            'view-navigation-item::r::a::m',
            'view-navigation-item::v::g::a',
            'view-navigation-item::penyimpanan',
            'view-navigation-item::d::v::d',
            'view-navigation-item::p::s::u',
            'view-navigation-item::keyboard',
            'view-navigation-item::mouse',
            'view-navigation-item::monitor',
            'view-navigation-item::headphone',
            'view-navigation-item::laboratorium',
            'view-navigation-item::klasifikasi::lab',
            'view-navigation-item::lapor::ptpp',
        ];

        $allLaboranPermissions = array_values(array_unique(array_merge(
            $inventoryPermissions,
            $navigationPermissions
        )));

        foreach ($allLaboranPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        /*
         * Role lama sesuai data teman:
         * Laboran_D2A sampai Laboran_D3N.
         */
        $labs = [
            'd2a' => 'Laboran_D2A',
            'd2b' => 'Laboran_D2B',
            'd2c' => 'Laboran_D2C',
            'd2d' => 'Laboran_D2D',
            'd2e' => 'Laboran_D2E',
            'd2f' => 'Laboran_D2F',
            'd2g' => 'Laboran_D2G',
            'd2h' => 'Laboran_D2H',
            'd2i' => 'Laboran_D2I',
            'd2j' => 'Laboran_D2J',
            'd2k' => 'Laboran_D2K',
            'd3l' => 'Laboran_D3L',
            'd3m' => 'Laboran_D3M',
            'd3n' => 'Laboran_D3N',
        ];

        /*
         * Buat permission dari data lab yang ada di tabel laboratoria.
         */
        if (Schema::hasTable('laboratoria') && Laboratorium::count() > 0) {
            $this->createLabPermissionsFromTable();
        }

        /*
         * Pastikan permission lama lab_d2a_view, dst selalu ada.
         * Ini mencegah error "There is no permission named lab_d2a_view".
         */
        foreach (array_keys($labs) as $slug) {
            $this->createLabPermissionsBySlug($slug);
        }

        foreach ($labs as $slug => $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->givePermissionTo($allLaboranPermissions);
            $role->givePermissionTo("lab_{$slug}_view");
        }

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $superAdminRole->syncPermissions(
            Permission::where('guard_name', 'web')->pluck('name')->toArray()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✅ Roles and permissions created successfully!');
    }

    protected function createLabPermissionsFromTable(): void
    {
        Laboratorium::orderBy('ruang')->get()->each(function (Laboratorium $lab) {
            $slug = strtolower(str_replace(['LAB ', ' ', '.'], ['', '_', '_'], $lab->ruang));

            $this->createLabPermissionsBySlug($slug);
        });
    }

    protected function createLabPermissionsBySlug(string $slug): void
    {
        foreach (['view', 'manage', 'edit', 'delete'] as $action) {
            Permission::firstOrCreate([
                'name' => "lab_{$slug}_{$action}",
                'guard_name' => 'web',
            ]);
        }
    }
}
