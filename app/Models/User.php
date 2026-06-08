<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasLabPermissions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasLabPermissions, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'npp', 'no_phone', 'position', 'tanggal_masuk', 'tanggal_keluar'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "User telah di-{$eventName}")
            ->useLogName('user');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_phone',
        'npp',
        'position',
        'foto',
        'tanggal_masuk',
        'tanggal_keluar',
        'is_active',
    ];

    /**
     * Boot method: auto-deactivate users whose contract has expired.
     */
    protected static function booted(): void
    {
        static::retrieved(function (User $user) {
            // Skip super_admin from auto-deactivation
            if ($user->hasRole('super_admin')) {
                return;
            }

            if (
                $user->tanggal_keluar !== null
                && $user->tanggal_keluar < Carbon::today()
                && $user->is_active
            ) {
                $user->updateQuietly(['is_active' => false]);
            }
        });
    }

    public function canAccessPanel(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the user's contract has expired.
     */
    public function isContractExpired(): bool
    {
        return $this->tanggal_keluar !== null
            && $this->tanggal_keluar < Carbon::today();
    }

    /**
     * Scope: filter by contract status.
     */
    public function scopeContractStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'aktif' => $query->where('is_active', true),
            'nonaktif' => $query->where('is_active', false),
            'berakhir_bulan_ini' => $query->whereNotNull('tanggal_keluar')
                ->whereYear('tanggal_keluar', Carbon::now()->year)
                ->whereMonth('tanggal_keluar', Carbon::now()->month),
            'berakhir_tahun_ini' => $query->whereNotNull('tanggal_keluar')
                ->whereYear('tanggal_keluar', Carbon::now()->year),
            default => $query, // 'semua'
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'npp';
    }
}
