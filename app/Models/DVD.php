<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DVD extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "DVD telah di-{$eventName}")
            ->useLogName('hardware');
    }

        protected $fillable = [
        'merk',
        'dvd',
        'spesifikasi',
        'tahun',
        'bulan',
        'stok',
    ];

    /**
     * Accessor untuk mendapatkan nama lengkap (merk + dvd)
     */
    public function getFullNameAttribute(): string
    {
        return $this->merk . '-' . $this->dvd;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dvd) {
            $lastId = self::max('id') + 1; // Ambil ID terakhir & tambahkan 1
            $kodeUnik = str_pad($lastId, 3, '0', STR_PAD_LEFT); // Format 001, 002, dst.
            $dvd->no_inventaris = 'LABKOM/DVD' . $kodeUnik . '/' . $dvd->tahun;
        });
    }
}
