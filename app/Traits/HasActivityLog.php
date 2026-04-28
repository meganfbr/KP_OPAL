<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait HasActivityLog
{
    /**
     * Boot the trait.
     */
    protected static function bootHasActivityLog(): void
    {
        static::created(function ($model) {
            $model->recordActivity('CREATE');
        });

        static::updated(function ($model) {
            $model->recordActivity('UPDATE');
        });

        static::deleted(function ($model) {
            $model->recordActivity('DELETE');
        });
    }

    /**
     * Record the activity.
     *
     * @param string $aksi
     * @return void
     */
    public function recordActivity(string $aksi): void
    {
        if (!Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $modul = $this->getActivityModul();
        $deskripsi = $this->getActivityDeskripsi($aksi);
        $labId = $this->getActivityLabId();

        ActivityLog::create([
            'user_id' => $userId,
            'aksi' => $aksi,
            'modul' => $modul,
            'deskripsi' => $deskripsi,
            'lab_id' => $labId,
            'created_at' => now(),
        ]);
    }

    /**
     * Get the module name.
     */
    protected function getActivityModul(): string
    {
        if (property_exists($this, 'activityModul')) {
            return $this->activityModul;
        }

        return Str::title(Str::snake(class_basename($this), ' '));
    }

    /**
     * Get the description for the activity.
     */
    protected function getActivityDeskripsi(string $aksi): string
    {
        $actionText = [
            'CREATE' => 'Menambah',
            'UPDATE' => 'Mengubah',
            'DELETE' => 'Menghapus',
        ][$aksi] ?? $aksi;

        $modelName = $this->getActivityModul();
        $identifier = $this->name ?? $this->ruang ?? $this->kode_inventaris ?? $this->id;

        return "{$actionText} {$modelName}: {$identifier}";
    }

    /**
     * Get the laboratory ID associated with the model.
     */
    protected function getActivityLabId(): ?int
    {
        // Try direct attribute
        if (isset($this->laboratorium_id)) return $this->laboratorium_id;
        if (isset($this->lab_id)) return $this->lab_id;
        
        // Try relationship if available
        if (method_exists($this, 'laboratorium') && $this->laboratorium) {
            return $this->laboratorium->id;
        }

        return null;
    }
}
