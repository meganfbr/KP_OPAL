<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Gate;

class KalenderAkademikWidget extends Widget
{
    // Set urutan widget di dashboard menjadi lebih tinggi prioritasnya
    protected static ?int $sort = 3;

    // Pastikan widget selalu terlihat
    protected static bool $isLazy = false;

    // Widget akan menggunakan setengah lebar (1 kolom dari default 2 kolom)
    // Pada mobile (default) akan full-width, pada md+ akan 1 kolom dari 2
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static string $view = 'filament.widgets.kalender-akademik-widget';

    // Fungsi untuk memeriksa apakah widget ini dapat ditampilkan berdasarkan izin
    public static function canView(): bool
    {
        return Gate::check('view-widget', 'KalenderAkademikWidget');
    }
}
