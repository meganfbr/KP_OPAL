ISI ZIP INI KHUSUS BAGIAN PERMISSION/PETUGAS

Tujuan:
- Mengambil bagian permission dari project pembanding.
- Dipakai untuk menyambungkan Inventaris PC dengan petugas/laboran otomatis.
- Jangan copy file Inventaris PC, Data Hardware, migration PC, atau model PC dari project pembanding karena project kamu sudah lebih update.

FILE YANG BOLEH DICOPY LANGSUNG:
1. app/Traits/HasLabPermissions.php
   Fungsi: memberi method hasLabPermission() dan getAuthorizedLabIds() ke User.
   Ini yang paling penting untuk petugas otomatis.

2. app/Providers/LabPermissionServiceProvider.php
   Fungsi: helper pembuatan nama permission lab. Service ini tidak berat karena createLabPermissions tidak jalan otomatis setiap request.

3. config/permission.php
   Fungsi: konfigurasi Spatie Permission. Copy hanya kalau project kamu belum punya config/permission.php.
   Kalau sudah ada, jangan overwrite tanpa dicek.

4. database/migrations/2025_07_07_132941_create_permission_tables.php
   Fungsi: membuat tabel permissions, roles, model_has_roles, model_has_permissions, dan role_has_permissions.
   Copy hanya kalau project kamu belum punya tabel/migration Spatie Permission.

5. database/seeders/RolePermissionSeeder.php
   Fungsi: membuat role super_admin dan role laboran per lab, serta permission lab_{slug}_view/manage/edit/delete.
   Boleh dipakai, tapi sesuaikan nama lab jika lab kamu sekarang Lab A-N atau Gudang.

FILE YANG JANGAN LANGSUNG OVERWRITE:
- app/Models/User.php
- app/Models/Laboratorium.php
- app/Filament/Resources/UserResource.php

Untuk file tersebut, pakai snippet di folder _manual_merge_snippets.

LANGKAH SETELAH COPY:
1. Pastikan composer punya Filament Shield atau Spatie Permission:
   composer require bezhansalleh/filament-shield
   atau
   composer require spatie/laravel-permission

2. Daftarkan provider jika belum ada di bootstrap/providers.php:
   App\Providers\LabPermissionServiceProvider::class,

3. Tambahkan trait ke app/Models/User.php:
   use Spatie\Permission\Traits\HasRoles;
   use App\Traits\HasLabPermissions;
   lalu pada class User: use HasRoles, HasLabPermissions;

4. Jalankan:
   composer dump-autoload
   php artisan optimize:clear
   php artisan migrate
   php artisan db:seed --class=RolePermissionSeeder

5. Pastikan PCInventoryResource.php menggunakan getAuthorizedLabIds('view') untuk mencari petugas.
