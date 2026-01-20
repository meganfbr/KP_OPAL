<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header dengan dropdown laboratorium -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Pilih Laboratorium
                </h2>

                <div class="max-w-md">
                    <select wire:model.live="selectedLabId"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option value="">-- Pilih Laboratorium --</option>
                        @foreach(\App\Models\Laboratorium::where('is_active', true)->orderBy('ruang')->get() as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->ruang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($selectedLabId)
            @php
                $selectedLab = \App\Models\Laboratorium::find($selectedLabId);
            @endphp

            <!-- Header Jadwal Lab -->
            <div style="background-color: #b91c1c !important;" class="rounded-xl shadow-lg border border-red-600">
                <div class="p-6">
                    <h2 style="color: white !important;" class="text-xl font-bold mb-1">
                        Penggunaan Ruang {{ $selectedLab?->ruang }}
                    </h2>
                    <p style="color: #fecaca !important;" class="text-sm">
                        Universitas Dian Nuswantoro {{ date('Y') }} / {{ date('Y') + 1 }}
                    </p>
                    <p style="color: #fca5a5 !important;" class="text-xs mt-1">
                        Jalan Nakula I nomor 5 - 11 Semarang Telepon (024) 3517261, 3520165
                    </p>
                </div>
            </div>

            <!-- Tabel Jadwal -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 border-b-2 border-gray-300 dark:border-gray-600">
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-gray-200 border-r border-gray-300 dark:border-gray-600 w-24">
                                    Hari
                                </th>
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-gray-200 border-r border-gray-300 dark:border-gray-600 w-32">
                                    Jadwal
                                </th>
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-gray-200 border-r border-gray-300 dark:border-gray-600">
                                    Nama Mata Kuliah
                                </th>
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-gray-200 border-r border-gray-300 dark:border-gray-600 w-28">
                                    Kelompok
                                </th>
                                <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-gray-200 w-64">
                                    Dosen
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $dayIndex => $day)
                                @php
                                    $daySchedules = $schedulesByDay[$day] ?? collect();
                                    $timeSlots = $this->getTimeSlots();
                                @endphp

                                @foreach($timeSlots as $slotIndex => $timeSlot)
                                    @php
                                        // Convert current slot to minutes for comparison
                                        $slotStart = \Carbon\Carbon::createFromFormat('H:i', $timeSlot);
                                        $slotEnd = $slotStart->copy()->addMinutes(50);

                                        // Find schedule that covers this time slot
                                        // A schedule covers this slot if: schedule.start_time <= slotStart AND schedule.end_time > slotStart
                                        $schedule = $daySchedules->first(function($s) use ($slotStart) {
                                            $scheduleStart = \Carbon\Carbon::parse($s->start_time);
                                            $scheduleEnd = \Carbon\Carbon::parse($s->end_time);

                                            // Check if this slot falls within the schedule's time range
                                            return $scheduleStart->lte($slotStart) && $scheduleEnd->gt($slotStart);
                                        });
                                    @endphp
                                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $slotIndex === 0 ? 'border-t-2 border-t-gray-400 dark:border-t-gray-500' : '' }}">
                                        @if($slotIndex === 0)
                                            <td class="px-4 py-2 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 align-top"
                                                rowspan="{{ count($timeSlots) }}">
                                                {{ $day }}
                                            </td>
                                        @endif

                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400 border-r border-gray-300 dark:border-gray-600 font-mono text-xs whitespace-nowrap">
                                            {{ $timeSlot }}-{{ $slotEnd->format('H:i') }}
                                        </td>

                                        @if($schedule)
                                            <td class="px-4 py-2 border-r border-gray-300 dark:border-gray-600 font-medium {{ $schedule->course ? 'text-gray-900 dark:text-white bg-blue-50 dark:bg-blue-900/20' : 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' }}">
                                                {{ $schedule->course ? strtoupper($schedule->course->name) : '(BELUM DIISI)' }}
                                            </td>
                                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600 {{ $schedule->course ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-orange-50 dark:bg-orange-900/20' }}">
                                                {{ $schedule->kelompok ?? '-' }}
                                            </td>
                                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 {{ $schedule->course ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-orange-50 dark:bg-orange-900/20' }}">
                                                {{ strtoupper($schedule->lecturer?->name ?? '-') }}
                                            </td>
                                        @else
                                            <td class="px-4 py-2 text-gray-300 dark:text-gray-600 border-r border-gray-300 dark:border-gray-600">
                                                -
                                            </td>
                                            <td class="px-4 py-2 text-gray-300 dark:text-gray-600 border-r border-gray-300 dark:border-gray-600">
                                                -
                                            </td>
                                            <td class="px-4 py-2 text-gray-300 dark:text-gray-600">
                                                -
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <!-- Pesan jika belum ada laboratorium yang dipilih -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 text-center border border-gray-200 dark:border-gray-700">
                <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Pilih Laboratorium</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Silakan pilih laboratorium di atas untuk melihat jadwal penggunaan ruang dalam format tabel.
                </p>
            </div>
        @endif

        {{-- Import Preview Section --}}
        @if($showImportPreview && !empty($importResults))
            <div class="fixed inset-0 bg-black/50 z-50 overflow-y-auto" wire:click.self="cancelImport">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-6xl w-full">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Preview Import Jadwal</h3>
                                <p class="text-sm text-gray-500">Matkul tidak ditemukan = null. Dosen baru = auto-create.</p>
                            </div>
                            <button wire:click="cancelImport" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-6 h-6"/>
                            </button>
                        </div>

                        <div class="p-6">
                        @foreach($importResults as $labName => $results)
                            <div class="mb-6">
                                <h4 class="font-semibold text-lg mb-3 text-gray-800 dark:text-gray-200">
                                    <x-heroicon-o-building-office class="w-5 h-5 inline mr-1"/>
                                    {{ $labName }}
                                    <span class="text-sm font-normal text-gray-500">({{ count($results) }} baris)</span>
                                </h4>

                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Status</th>
                                                <th class="px-3 py-2 text-left">Hari</th>
                                                <th class="px-3 py-2 text-left">Jadwal</th>
                                                <th class="px-3 py-2 text-left">Mata Kuliah</th>
                                                <th class="px-3 py-2 text-left">Kelompok</th>
                                                <th class="px-3 py-2 text-left">Dosen</th>
                                                <th class="px-3 py-2 text-left">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($results as $index => $result)
                                                <tr class="border-t {{ $result['status'] === 'error' ? 'bg-red-50 dark:bg-red-900/20' : ($result['status'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-green-50 dark:bg-green-900/20') }}">
                                                    <td class="px-3 py-2">
                                                        @if($result['status'] === 'valid')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                ✓ Valid
                                                            </span>
                                                        @elseif($result['status'] === 'warning')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                ⚠ Warning
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                ✗ Error
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">{{ $result['day'] }}</td>
                                                    <td class="px-3 py-2 font-mono text-xs">{{ $result['jadwal'] }}</td>
                                                    <td class="px-3 py-2">
                                                        @if($result['course_id'])
                                                            {{ $result['course_name'] ?? $result['mata_kuliah'] }}
                                                        @else
                                                            <span class="text-gray-400 italic">{{ $result['mata_kuliah'] }} (null)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">{{ $result['kelompok'] }}</td>
                                                    <td class="px-3 py-2">
                                                        {{ $result['lecturer_name'] ?? $result['dosen'] }}
                                                        @if(!empty($result['create_lecturer']))
                                                            <span class="text-xs text-blue-600">(Baru)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-xs">
                                                        @foreach($result['errors'] as $error)
                                                            <div class="text-red-600">{{ $error }}</div>
                                                        @endforeach
                                                        @foreach($result['warnings'] as $warning)
                                                            <div class="text-yellow-600">{{ $warning }}</div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        <div class="text-sm text-gray-500">
                            @php
                                $totalValid = 0;
                                $totalError = 0;
                                foreach($importResults as $results) {
                                    foreach($results as $r) {
                                        if($r['status'] === 'error') $totalError++;
                                        else $totalValid++;
                                    }
                                }
                            @endphp
                            <span class="text-green-600 font-medium">{{ $totalValid }} valid</span> •
                            <span class="text-red-600 font-medium">{{ $totalError }} error</span>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click="cancelImport" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                                Batal
                            </button>
                            <button wire:click="confirmImport" class="px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700"
                                    {{ $totalValid === 0 ? 'disabled' : '' }}>
                                Import {{ $totalValid }} Jadwal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
