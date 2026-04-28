<div class="space-y-2">
    @if(is_array($komponen) && count($komponen) > 0)
        @foreach($komponen as $item)
            <div class="p-2 rounded bg-gray-50 dark:bg-gray-800 border dark:border-gray-700">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-sm text-primary-600 dark:text-primary-400">
                        {{ is_array($item) ? $item['komponen'] : $item }}
                    </span>
                    @if(is_array($item) && !empty($item['kondisi']))
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                            {{ $item['kondisi'] }}
                        </span>
                    @endif
                </div>
                @if(is_array($item) && !empty($item['keterangan']))
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $item['keterangan'] }}
                    </p>
                @else
                    <p class="text-sm italic text-gray-400 dark:text-gray-500">
                        Tidak ada keterangan khusus
                    </p>
                @endif
            </div>
        @endforeach
    @else
        <p class="text-sm italic text-gray-500">Tidak ada komponen yang dilaporkan rusak.</p>
    @endif
</div>
