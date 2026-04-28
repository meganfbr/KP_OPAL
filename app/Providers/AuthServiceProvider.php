<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Mendaftarkan gate untuk memeriksa izin navigasi
        Gate::define('view-navigation-item', function (User $user, string $permission) {
            // Super admin selalu memiliki akses
            if ($user->hasRole('super_admin')) {
                return true;
            }

            // Untuk debugging - lihat semua izin yang dimiliki user
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

            // Kasus khusus untuk RAM, VGA, DVD, PSU yang menggunakan format ::
            $specialCases = [
                'r::a::m' => ['ram', 'r_a_m', 'r::a::m'],
                'v::g::a' => ['vga', 'v_g_a', 'v::g::a'],
                'd::v::d' => ['dvd', 'd_v_d', 'd::v::d'],
                'p::s::u' => ['psu', 'p_s_u', 'p::s::u'],
            ];

            if (array_key_exists($permission, $specialCases)) {
                foreach ($specialCases[$permission] as $permFormat) {
                    if ($user->can("view_{$permFormat}") || $user->can("view_any_{$permFormat}")) {
                        return true;
                    }

                    // Periksa juga dalam daftar semua izin user
                    foreach ($userPermissions as $userPerm) {
                        if (Str::contains($userPerm, "view_any_{$permFormat}") ||
                            Str::contains($userPerm, "view_{$permFormat}")) {
                            return true;
                        }
                    }
                }
                return false;
            }

            // Jika izin adalah untuk resource yang harus diperiksa
            if (str_contains($permission, '::')) {
                // Format permission name (replace :: dengan _)
                $normalizedPermission = str_replace('::', '_', $permission);

                // Cek jika pengguna memiliki izin view atau view_any
                if ($user->can("view_{$normalizedPermission}") ||
                    $user->can("view_any_{$normalizedPermission}")) {
                    return true;
                }

                // Cek juga format alternatif untuk permisi (view_lapor::ptpp vs view_lapor_ptpp)
                $alternativePermission = $permission;
                if ($user->can("view_{$alternativePermission}") ||
                    $user->can("view_any_{$alternativePermission}")) {
                    return true;
                }

                // Cek juga dengan format yang terpisah (lapor_ptpp menjadi lapor ptpp)
                $parts = explode('_', $normalizedPermission);
                if (count($parts) > 1) {
                    foreach ($userPermissions as $userPerm) {
                        if (Str::contains($userPerm, $parts[0]) && Str::contains($userPerm, $parts[1])) {
                            return true;
                        }
                    }
                }
            } else {
                // Untuk resource standar
                if ($user->can("view_{$permission}") ||
                    $user->can("view_any_{$permission}")) {
                    return true;
                }

                // Cek juga dengan format yang lebih spesifik untuk kasus PTPP
                if ($permission == 'lapor_ptpp' || $permission == 'lapor::ptpp') {
                    foreach ($userPermissions as $userPerm) {
                        if (Str::contains($userPerm, 'ptpp') || Str::contains($userPerm, 'lapor')) {
                            return true;
                        }
                    }
                }
            }

            return false;
        });

        // Gate untuk memeriksa izin widget
        Gate::define('view-widget', function (User $user, string $widgetName) {
            // Super admin selalu memiliki akses
            if ($user->hasRole('super_admin')) {
                return true;
            }

            // Izinkan semua pengguna (termasuk Laboran) untuk melihat widget umum
            if (in_array($widgetName, ['WelcomeWidget', 'CalendarWidget', 'KalenderAkademikWidget'])) {
                return true;
            }

            $permissionName = "widget_{$widgetName}";

            // Periksa izin widget
            return $user->hasPermissionTo($permissionName);
        });
    }
}
