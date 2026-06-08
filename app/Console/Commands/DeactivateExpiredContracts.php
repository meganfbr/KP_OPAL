<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeactivateExpiredContracts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:deactivate-expired';

    /**
     * The console command description.
     */
    protected $description = 'Menonaktifkan akun laboran yang kontraknya sudah berakhir (tanggal_keluar < hari ini)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        $expiredUsers = User::where('is_active', true)
            ->whereNotNull('tanggal_keluar')
            ->whereDate('tanggal_keluar', '<', $today)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            })
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('Tidak ada akun laboran yang perlu dinonaktifkan.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($expiredUsers as $user) {
            $user->update(['is_active' => false]);
            $rows[] = [
                $user->name,
                $user->npp,
                $user->tanggal_keluar->format('d M Y'),
            ];
        }

        $this->info("Berhasil menonaktifkan {$expiredUsers->count()} akun laboran:");
        $this->table(['Nama', 'NPP', 'Tanggal Keluar'], $rows);

        return self::SUCCESS;
    }
}
