<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Monitor extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Monitor telah di-{$eventName}")
            ->useLogName('monitor');
    }

        protected $fillable = [
        'merk',
        'nama',
        'resolusi',
        'ukuran',
        'spesifikasi',
        'tahun',
        'bulan',
        'stok',
    ];

    /**
     * Accessor untuk mendapatkan nama lengkap (merk + nama + ukuran)
     */
    public function getFullNameAttribute(): string
    {
        return $this->merk . '-' . $this->nama . '-' . $this->ukuran . 'inch';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($monitors) {
            $lastId = self::max('id') + 1; // Ambil ID terakhir & tambahkan 1
            $kodeUnik = str_pad($lastId, 3, '0', STR_PAD_LEFT); // Format 001, 002, dst.
            $monitors->no_inventaris = 'LABKOM/MON/' . $kodeUnik . '/' . $monitors->tahun;
        });
    }
}
