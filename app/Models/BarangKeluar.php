<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

use App\Traits\HasActivityLog;

class BarangKeluar extends Model
{
    use HasActivityLog;

    protected $activityModul = 'Barang Keluar';

    protected $table = 'barang_keluar';

    protected $fillable = [
        'no_inventaris',
        'nama_barang',
        'jumlah',
        'tanggal',
        'laboratorium_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // Temporarily commenting out the global scope to debug 500 error
        /*
        static::addGlobalScope('lab-permissions', function (Builder $builder) {
            // Skip scope for console commands or when no user is authenticated
            if (app()->runningInConsole() || !Auth::check()) {
                return;
            }

            $user = Auth::user();

            // Super admin can see all data, no filtering needed
            if ($user->hasRole('super_admin')) {
                return;
            }

            // For all other users, only show items from labs they have permission to access
            $authorizedLabIds = $user->getAuthorizedLabIds('view');
            $builder->whereIn('laboratorium_id', $authorizedLabIds);
        });
        */
    }

    /**
     * Get the laboratory that this item belongs to.
     */
    public function laboratorium(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class);
    }
}
