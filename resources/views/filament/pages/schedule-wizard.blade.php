<x-filament-panels::page>
    {{-- Import Button in Header --}}
    <div class="flex justify-between items-center mb-6">
        <div></div>
        <x-filament::button wire:click="toggleImportModal" color="success" icon="heroicon-o-arrow-up-tray">
            Import Excel
        </x-filament::button>
    </div>

    {{-- Import Modal --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden">
                {{-- Modal Header --}}
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Import Jadwal dari Excel
                    </h2>
                    <button wire:click="cancelImport" class="text-gray-500 hover:text-gray-700">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                    @if(!$showImportPreview)
                        {{-- Upload Section --}}
                        <div class="space-y-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Format Excel:</h4>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Kolom: <code>Prodi | Kdmk | Nama Mk | Lab | pagi | malam | Kelas | Sks</code>
                                </p>
                            </div>

                            <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                                <p class="text-sm text-amber-700 dark:text-amber-300">
                                    ⚠️ Mode <strong>REPLACE</strong>: Semua jadwal existing akan dihapus!
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Pilih File Excel (.xlsx, .xls)
                                </label>
                                <input type="file" wire:model="importFile" accept=".xlsx,.xls"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                                
                                @if($importFile)
                                    <p class="mt-2 text-sm text-green-600">✓ File dipilih: {{ $importFile->getClientOriginalName() }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Preview Section --}}
                        <div class="space-y-4">
                            {{-- Summary --}}
                            <div class="grid grid-cols-4 gap-4">
                                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $importSummary['total'] ?? 0 }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900/30 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $importSummary['success'] ?? 0 }}</div>
                                    <div class="text-sm text-green-600 dark:text-green-400">OK</div>
                                </div>
                                <div class="bg-amber-100 dark:bg-amber-900/30 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $importSummary['warning'] ?? 0 }}</div>
                                    <div class="text-sm text-amber-600 dark:text-amber-400">Warning</div>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900/30 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $importSummary['error'] ?? 0 }}</div>
                                    <div class="text-sm text-red-600 dark:text-red-400">Error</div>
                                </div>
                            </div>

                            {{-- Unplotted Warning --}}
                            @if(count($unplottedSchedules) > 0)
                                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                    <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">
                                        ⚠️ {{ count($unplottedSchedules) }} jadwal tidak bisa diplot:
                                    </h4>
                                    <div class="overflow-x-auto max-h-48 overflow-y-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-red-100 dark:bg-red-900/40">
                                                <tr>
                                                    <th class="px-2 py-1 text-left text-xs font-medium text-red-700 dark:text-red-300">Kelompok</th>
                                                    <th class="px-2 py-1 text-left text-xs font-medium text-red-700 dark:text-red-300">Mata Kuliah</th>
                                                    <th class="px-2 py-1 text-left text-xs font-medium text-red-700 dark:text-red-300">SKS</th>
                                                    <th class="px-2 py-1 text-left text-xs font-medium text-red-700 dark:text-red-300">Alasan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-red-200 dark:divide-red-800">
                                                @foreach($unplottedSchedules as $unplotted)
                                                    <tr>
                                                        <td class="px-2 py-1 text-red-700 dark:text-red-300">{{ $unplotted['kelompok'] }}</td>
                                                        <td class="px-2 py-1 text-red-700 dark:text-red-300">{{ Str::limit($unplotted['nama_mk'], 20) }}</td>
                                                        <td class="px-2 py-1 text-red-700 dark:text-red-300">{{ $unplotted['sks'] }}</td>
                                                        <td class="px-2 py-1 text-red-600 dark:text-red-400 font-medium">{{ $unplotted['message'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Preview Table --}}
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prodi</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kelompok</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Mata Kuliah</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">SKS</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hari</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lab</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($this->getPaginatedResults() as $result)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    @if($result['status'] === 'ok')
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">OK</span>
                                                    @elseif($result['status'] === 'warning')
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Warning</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Error</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $result['prodi_code'] }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $result['kelompok'] }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($result['nama_mk'], 25) }}</td>
                                                <td class="px-3 py-2 text-sm whitespace-nowrap">
                                                    @if(isset($result['sks_db']) && $result['sks_db'] != $result['sks'])
                                                        <span class="text-amber-600 dark:text-amber-400 font-semibold" title="Excel: {{ $result['sks'] }}, DB: {{ $result['sks_db'] }}">
                                                            {{ $result['sks'] }} ⚠️
                                                        </span>
                                                    @else
                                                        {{ $result['sks'] }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $result['day'] }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $result['start_time'] }} - {{ $result['end_time'] }}</td>
                                                <td class="px-3 py-2 text-sm whitespace-nowrap">
                                                    @if($result['laboratorium_name'] !== '-')
                                                        <span class="{{ ($result['is_priority'] ?? false) ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-600 dark:text-gray-400' }}">
                                                            {{ $result['laboratorium_name'] }}
                                                            @if($result['is_priority'] ?? false)
                                                                <span class="ml-1 px-1.5 py-0.5 text-xs rounded bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">★</span>
                                                            @endif
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $result['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                {{-- Pagination Controls --}}
                                @if($this->getTotalPages() > 1)
                                    <div class="flex items-center justify-between mt-4 px-4 py-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Halaman {{ $previewPage }} dari {{ $this->getTotalPages() }} 
                                            ({{ count($importResults) }} jadwal)
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="prevPage" 
                                                class="px-3 py-1 text-sm font-medium rounded-lg border
                                                    {{ $previewPage > 1 
                                                        ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 border-gray-300 dark:border-gray-600' 
                                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700' }}"
                                                {{ $previewPage <= 1 ? 'disabled' : '' }}>
                                                ← Sebelumnya
                                            </button>
                                            
                                            {{-- Page numbers --}}
                                            @php
                                                $totalPages = $this->getTotalPages();
                                                $startPage = max(1, $previewPage - 2);
                                                $endPage = min($totalPages, $previewPage + 2);
                                            @endphp
                                            
                                            @for($page = $startPage; $page <= $endPage; $page++)
                                                <button wire:click="goToPage({{ $page }})"
                                                    class="px-3 py-1 text-sm font-medium rounded-lg
                                                        {{ $page === $previewPage 
                                                            ? 'bg-primary-600 text-white' 
                                                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' }}">
                                                    {{ $page }}
                                                </button>
                                            @endfor
                                            
                                            <button wire:click="nextPage"
                                                class="px-3 py-1 text-sm font-medium rounded-lg border
                                                    {{ $previewPage < $this->getTotalPages() 
                                                        ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 border-gray-300 dark:border-gray-600' 
                                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700' }}"
                                                {{ $previewPage >= $this->getTotalPages() ? 'disabled' : '' }}>
                                                Selanjutnya →
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mt-2 text-center">
                                        Total {{ count($importResults) }} jadwal
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-3 p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <x-filament::button wire:click="cancelImport" color="gray">
                        Batal
                    </x-filament::button>
                    @if(!$showImportPreview)
                        <x-filament::button wire:click="processImport" color="primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="processImport">Memproses...</span>
                            <span wire:loading.remove wire:target="processImport">Preview</span>
                        </x-filament::button>
                    @else
                        <x-filament::button wire:click="confirmImport" color="success" wire:loading.attr="disabled">
                            <span wire:loading wire:target="confirmImport">Mengimport...</span>
                            <span wire:loading.remove wire:target="confirmImport">Konfirmasi Import</span>
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Original Form --}}
    <form wire:submit="findAvailableSlots" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                Cari Slot Tersedia
            </x-filament::button>

            @if($showRecommendations)
                <x-filament::button type="button" color="gray" wire:click="resetRecommendations">
                    Reset
                </x-filament::button>
            @endif
        </div>
    </form>

    @if($showRecommendations)
        <div class="mt-8">
            {{-- Course Info Banner --}}
            @if($this->course)
                <div
                    class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-4 mb-6 border border-primary-200 dark:border-primary-800">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-academic-cap class="w-8 h-8 text-primary-600" />
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-white">
                                {{ $this->course->name }}
                                @if($data['kelompok'] ?? null)
                                    <span class="text-primary-600">(Kelompok {{ $data['kelompok'] }})</span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $this->course->sks }} SKS ({{ $this->course->sks * 50 }} menit)
                                @if($this->course->prodi)
                                    • {{ $this->course->prodi->name }}
                                @endif
                                @if($this->course->jumlah_mahasiswa > 0)
                                    • {{ $this->course->jumlah_mahasiswa }} mahasiswa
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Day Tabs --}}
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="flex space-x-1 overflow-x-auto" aria-label="Days">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                            @php
                                $dayRecs = $recommendations[$day] ?? [];
                                $count = count($dayRecs);
                                $isSelected = $selectedDay === $day;
                            @endphp
                            <button type="button" wire:click="selectDay('{{ $day }}')"
                                class="px-4 py-3 text-sm font-medium rounded-t-lg transition-colors whitespace-nowrap
                                            {{ $isSelected
                        ? 'bg-primary-600 text-white'
                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800' }}">
                                {{ $day }}
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs 
                                            {{ $isSelected ? 'bg-primary-500 text-white' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    {{ $count }}
                                </span>
                            </button>
                    @endforeach
                </nav>
            </div>

            {{-- Recommendations Grid --}}
            @if($selectedDay && isset($recommendations[$selectedDay]))
                @php $dayRecommendations = $recommendations[$selectedDay]; @endphp

                @if(count($dayRecommendations) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($dayRecommendations as $rec)
                            <div wire:click="createSchedule({{ $rec['lab_id'] }}, {{ $rec['slot_id'] }})" class="group cursor-pointer bg-white dark:bg-gray-800 rounded-xl border-2 
                                                    {{ $rec['is_priority']
                                    ? 'border-amber-400 hover:border-amber-500 hover:shadow-amber-100'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-primary-400' }}
                                                    hover:shadow-lg transition-all duration-200 p-4 relative overflow-hidden">
                                {{-- Priority Badge --}}
                                @if($rec['is_priority'])
                                    <div
                                        class="absolute top-0 right-0 bg-amber-400 text-amber-900 text-xs font-bold px-2 py-1 rounded-bl-lg">
                                        ⭐ Prioritas
                                    </div>
                                @endif

                                {{-- Lab Name --}}
                                <div class="flex items-center gap-2 mb-3">
                                    <x-heroicon-o-building-office class="w-5 h-5 text-primary-600" />
                                    <span class="font-bold text-lg text-gray-900 dark:text-white">
                                        {{ $rec['lab_name'] }}
                                    </span>
                                </div>

                                {{-- Time --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <x-heroicon-o-clock class="w-5 h-5 text-green-600" />
                                    <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $rec['start_time'] }} - {{ $rec['end_time'] }}
                                    </span>
                                </div>

                                {{-- Capacity --}}
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-computer-desktop class="w-4 h-4" />
                                    <span>{{ $rec['lab_capacity'] }} PC tersedia</span>
                                </div>

                                {{-- Hover instruction --}}
                                <div
                                    class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-xs text-primary-600 dark:text-primary-400 font-medium">
                                        Klik untuk membuat jadwal →
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <x-heroicon-o-calendar class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <p class="text-gray-600 dark:text-gray-400">
                            Tidak ada slot tersedia untuk hari <strong>{{ $selectedDay }}</strong>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Coba pilih hari lain atau periksa ketersediaan lab.
                        </p>
                    </div>
                @endif
            @endif
        </div>
    @endif
</x-filament-panels::page>