<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogAuthenticationActions
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'aksi' => 'LOGIN',
            'modul' => 'Autentikasi',
            'deskripsi' => 'Pengguna ' . $event->user->name . ' telah masuk ke sistem',
            'lab_id' => null,
            'created_at' => now(),
        ]);
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if (!$event->user) {
            return;
        }

        ActivityLog::create([
            'user_id' => $event->user->id,
            'aksi' => 'LOGOUT',
            'modul' => 'Autentikasi',
            'deskripsi' => 'Pengguna ' . $event->user->name . ' telah keluar dari sistem',
            'lab_id' => null,
            'created_at' => now(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
