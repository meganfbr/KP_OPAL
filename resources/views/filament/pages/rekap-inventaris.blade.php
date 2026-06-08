<x-filament-panels::page>
    <div class="mb-6">
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900 shadow-sm">
            <div class="space-y-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="inline-flex items-center rounded-full bg-primary-100 px-3 py-1 text-sm font-semibold text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                        {{ strtoupper($this->laboratoriumNama ?? 'Laboratorium') }}
                    </span>
                    <h2 class="text-xl font-bold">
                        Rekap Inventaris — Periode {{ $this->periodeLabel }}
                    </h2>
                    @if (auth()->user()->hasRole('super_admin'))
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Mode Hanya Lihat (Super Admin)
                        </span>
                    @endif
                </div>
                
                <div class="border-t pt-4 dark:border-gray-700">
                    {{ $this->form }}
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                    @if (auth()->user()->hasRole('super_admin'))
                        * Pilih ruangan dan periode di atas untuk melihat data rekap. Sebagai Super Admin, Anda hanya dapat melihat dan mengunduh data.
                    @else
                        * Pilih periode di atas untuk memfilter data. Klik kode pada kolom "Spek" untuk melihat detail spesifikasi.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        @if($this->periodeId)
            @livewire(
                'rekap-inventaris.pc-table',
                [
                    'periodeId'      => $this->periodeId,
                    'bulan'          => $this->bulan,
                    'tahun'          => $this->tahun,
                    'laboratoriumId' => $this->laboratoriumId,
                ],
                key('pc-table-' . $this->periodeId . '-' . ($this->laboratoriumId ?? 'all'))
            )

            <div class="pt-8">
                @livewire(
                    'rekap-inventaris.non-pc-table',
                    [
                        'periodeId'      => $this->periodeId,
                        'bulan'          => $this->bulan,
                        'tahun'          => $this->tahun,
                        'laboratoriumId' => $this->laboratoriumId,
                    ],
                    key('non-pc-table-' . $this->periodeId . '-' . ($this->laboratoriumId ?? 'all'))
                )
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center">
                <div class="text-gray-400 dark:text-gray-500">
                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="font-semibold text-lg">Belum Ada Data Rekap</p>
                    <p class="mt-1 text-sm">Laboratorium ini belum memiliki data rekap inventaris untuk periode manapun.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>