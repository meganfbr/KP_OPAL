<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sks',
        'prodi_id',
        'software_requirements'
    ];

    protected $casts = [
        'software_requirements' => 'array'
    ];

    /**
     * Relasi ke model Prodi
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Relasi many-to-many ke SoftwareDetail via tabel pivot course_software
     */
    public function software(): BelongsToMany
    {
        return $this->belongsToMany(SoftwareDetail::class, 'course_software');
    }
}
