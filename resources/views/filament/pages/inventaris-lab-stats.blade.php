<x-filament-panels::page>
    <div class="space-y-8">
        @foreach($labData as $lab)
            <x-filament::section :heading="$lab['ruang']">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Stats Card PC -->
                    <div class="col-span-1 space-y-3">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">PC Desktop</h3>
                        <div class="p-6 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-white/10 text-center flex flex-col justify-center items-center h-48">
                            <span class="text-6xl font-extrabold text-primary-600 dark:text-primary-400">{{ $lab['pc_count'] }}</span>
                            <span class="block text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Total Unit Terdaftar</span>
                        </div>
                    </div>

                    <!-- Non-PC Table -->
                    <div class="col-span-1 lg:col-span-1 space-y-3">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Non-PC</h3>
                        @if(count($lab['non_pcs']) > 0)
                            <div class="overflow-x-auto overflow-y-auto max-h-48 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm custom-scrollbar">
                                <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Nama / Model</th>
                                            <th class="px-4 py-3 font-semibold text-center">Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-gray-900">
                                        @foreach($lab['non_pcs'] as $npc)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $npc['nama'] }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $npc['versi'] }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-sm font-bold text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                                        {{ $npc['qty'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 p-6 border rounded-xl border-dashed text-center bg-gray-50/50 dark:bg-gray-800/20 font-medium">Belum ada data barang Non-PC.</div>
                        @endif
                    </div>

                    <!-- Software Table -->
                    <div class="col-span-1 lg:col-span-1 space-y-3">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Software</h3>
                        @if(count($lab['softwares']) > 0)
                            <div class="overflow-x-auto overflow-y-auto max-h-48 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm custom-scrollbar">
                                <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Software / Versi</th>
                                            <th class="px-4 py-3 font-semibold text-center">Instalasi / Info</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-gray-900">
                                        @foreach($lab['softwares'] as $sw)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $sw['nama'] }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $sw['versi'] }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center rounded-md bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-700/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                                        {{ $sw['qty'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 p-6 border rounded-xl border-dashed text-center bg-gray-50/50 dark:bg-gray-800/20 font-medium">Belum ada data Software.</div>
                        @endif
                    </div>

                </div>
            </x-filament::section>
        @endforeach
    </div>

    <!-- Tambahkan style untuk custom-scrollbar agar rapi -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5); /* tailwind gray-400 */
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(75, 85, 99, 0.5); /* tailwind gray-600 */
        }
    </style>
</x-filament-panels::page>
