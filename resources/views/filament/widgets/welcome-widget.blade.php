<x-filament-widgets::widget>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center justify-between p-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight">
                    Selamat Datang, {{ $this->getUserName() }}!
                </h2>

                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Sistem Informasi Operasional Laboratorium Komputer Universitas Dian Nuswantoro
                </p>

                <p class="mt-3">
                    <span class="text-primary-600 font-medium">Hari ini: {{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                </p>
            </div>

            <div>
                <img src="{{ asset('images/udinus.png') }}" alt="Logo UDINUS" class="h-16 w-auto">
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
