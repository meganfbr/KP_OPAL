<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Motherboard extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Motherboard telah di-{$eventName}")
            ->useLogName('hardware');
    }

        protected $fillable = [
        'merk',
        'tipe',
        'tahun',
        'bulan',
        'stok',
    ];

    /**
     * Accessor untuk mendapatkan nama lengkap (merk + tipe)
     */
    public function getFullNameAttribute(): string
    {
        return $this->merk . '-' . $this->tipe;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($motherboard) {
            $lastId = self::max('id') + 1; // Ambil ID terakhir & tambahkan 1
            $kodeUnik = str_pad($lastId, 3, '0', STR_PAD_LEFT); // Format 001, 002, dst.
            $motherboard->no_inventaris = 'LABKOM/MB/' . $kodeUnik . '/' . $motherboard->tahun;
        });
    }
}
