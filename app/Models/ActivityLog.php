<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'aksi',
        'modul',
        'deskripsi',
        'lab_id',
        'created_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * Disable updated_at since this is a log.
     */
    public $timestamps = false; // We use created_at manually or via default

    /**
     * Relationship to the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to the laboratory where the action occurred.
     */
    public function laboratorium(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class, 'lab_id');
    }
}
