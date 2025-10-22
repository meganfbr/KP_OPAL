<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header dengan dropdown laboratorium -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Pilih Laboratorium
                </h2>

                <!-- Tombol Tambah Jadwal di header -->
                <div>
                    {{ $this->createAction }}
                </div>
            </div>

            <div class="max-w-md">
                <select wire:model.live="selectedLabId"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">-- Pilih Laboratorium --</option>
                    @foreach(\App\Models\Laboratorium::all() as $lab)
                        <option value="{{ $lab->id }}">{{ $lab->ruang }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($selectedLabId)
            <!-- Timetable Grid -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Jadwal Laboratorium: {{ \App\Models\Laboratorium::find($selectedLabId)?->ruang }}
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Header tabel -->
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-700 z-10 min-w-[120px] border-r border-gray-200 dark:border-gray-600">
                                    Waktu
                                </th>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                    <th scope="col"
                                        class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[180px] border-r border-gray-200 dark:border-gray-600">
                                        {{ $day }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <!-- Body tabel -->
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getTimeSlots() as $time)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <!-- Kolom waktu (sticky) -->
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-600 z-10">
                                        @php
                                            $startTime = \Carbon\Carbon::createFromFormat('H:i', $time);
                                            $endTime = $startTime->copy()->addMinutes(50);
                                        @endphp
                                        <div class="text-center">
                                            <div class="font-semibold">{{ $time }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $endTime->format('H:i') }}</div>
                                        </div>
                                    </td>

                                    <!-- Kolom hari -->
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                        <td class="px-1 py-1 text-sm text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600 min-h-[70px]">
                                            @php
                                                $schedule = $schedulesByTimeSlotAndDay[$time][$day] ?? null;
                                            @endphp

                                            @if($schedule)
                                                <!-- Sel terisi - klik untuk edit -->
                                                <button
                                                    wire:click="mountAction('edit', { 'scheduleId': {{ $schedule->id }} })"
                                                    class="w-full h-full min-h-[66px] text-left p-2 rounded-md bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:hover:bg-primary-800 border border-primary-300 dark:border-primary-700 transition-all duration-200 group relative overflow-hidden"
                                                >
                                                    <div class="relative z-10">
                                                        <!-- Nama mata kuliah -->
                                                        <div class="font-medium text-primary-900 dark:text-primary-100 text-xs mb-1 leading-tight">
                                                            {{ Str::limit($schedule->course->name ?? 'N/A', 25) }}
                                                        </div>

                                                        <!-- Kelompok (jika ada) -->
                                                        @if($schedule->kelompok && $schedule->kelompok !== 'Semua')
                                                            <div class="text-xs text-primary-700 dark:text-primary-300 mb-1">
                                                                Klp: {{ $schedule->kelompok }}
                                                            </div>
                                                        @endif

                                                        <!-- Nama dosen -->
                                                        <div class="text-xs text-primary-600 dark:text-primary-400 mb-1">
                                                            {{ Str::limit($schedule->lecturer->name ?? 'Belum Ditentukan', 20) }}
                                                        </div>

                                                        <!-- Waktu -->
                                                        <div class="text-xs text-primary-500 dark:text-primary-500">
                                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                        </div>
                                                    </div>

                                                    <!-- Hover effect overlay -->
                                                    <div class="absolute inset-0 bg-primary-600 opacity-0 group-hover:opacity-10 transition-opacity duration-200"></div>

                                                    <!-- Edit icon on hover -->
                                                    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        <div class="p-1 bg-white dark:bg-gray-800 rounded shadow-sm">
                                                            <svg class="w-3 h-3 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </button>
                                            @else
                                                <!-- Sel kosong - klik untuk create -->
                                                <button
                                                    wire:click="mountAction('create', { 'day': '{{ $day }}', 'time': '{{ $time }}' })"
                                                    class="w-full h-full min-h-[66px] text-left p-2 rounded-md border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group flex items-center justify-center"
                                                    title="Klik untuk menambah jadwal pada {{ $day }} jam {{ $time }}"
                                                >
                                                    <!-- Plus icon (hidden by default, shown on hover) -->
                                                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-600 group-hover:text-primary-500 dark:group-hover:text-primary-400 opacity-0 group-hover:opacity-100 transition-all duration-200"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Keterangan -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Keterangan:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-primary-100 dark:bg-primary-900 border border-primary-300 dark:border-primary-700 rounded"></div>
                        <span>Jadwal yang sudah ada</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded"></div>
                        <span>Slot kosong (klik untuk menambah)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Klik jadwal untuk edit/hapus</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 px-2 py-1 rounded">1 SKS = 50 menit</span>
                        <span>Durasi otomatis</span>
                    </div>

                    <!-- Informasi tambahan -->
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Tips:</strong> Jadwal dengan durasi multi-SKS akan mengisi beberapa slot waktu secara otomatis.
                                Klik pada slot kosong untuk menambah jadwal baru, atau klik pada jadwal yang ada untuk mengedit.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Pesan jika belum ada laboratorium yang dipilih -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Pilih Laboratorium</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Silakan pilih laboratorium di atas untuk melihat dan mengelola jadwal dalam format timetable visual.
                </p>
            </div>
        @endif
    </div>

    <!-- Modal untuk create/edit/delete actions -->
    <x-filament-actions::modals />
</x-filament-panels::page>
