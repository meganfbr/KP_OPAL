<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="font-bold block text-sm text-gray-500">Aktor</span>
            <span>{{ $record->causer ? $record->causer->name : 'Sistem' }}</span>
        </div>
        <div>
            <span class="font-bold block text-sm text-gray-500">Waktu</span>
            <span>{{ $record->created_at->format('d M Y, H:i:s') }}</span>
        </div>
        <div>
            <span class="font-bold block text-sm text-gray-500">Aksi</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                {{ strtoupper($record->event) }}
            </span>
        </div>
        <div>
            <span class="font-bold block text-sm text-gray-500">Model</span>
            <span>{{ $record->subject_type ? class_basename($record->subject_type) : '-' }}</span>
        </div>
    </div>

    @if($record->description)
        <div>
            <span class="font-bold block text-sm text-gray-500">Deskripsi</span>
            <p>{{ $record->description }}</p>
        </div>
    @endif

    @if($record->properties->count() > 0)
        <div>
            <span class="font-bold block text-sm text-gray-500 mb-2">Detail Perubahan</span>
            
            @if(isset($record->properties['old']) && isset($record->properties['attributes']))
                <!-- Update case -->
                <div class="border rounded shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atribut</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Lama</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($record->properties['attributes'] as $key => $newValue)
                                <tr>
                                    <td class="px-4 py-2 font-medium bg-gray-50">{{ $key }}</td>
                                    <td class="px-4 py-2 text-red-600 bg-red-50 line-through">
                                        {{ is_array($record->properties['old'][$key] ?? null) ? json_encode($record->properties['old'][$key]) : ($record->properties['old'][$key] ?? '-') }}
                                    </td>
                                    <td class="px-4 py-2 text-green-600 bg-green-50">
                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(isset($record->properties['attributes']))
                <!-- Create case -->
                <div class="border rounded shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atribut</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($record->properties['attributes'] as $key => $value)
                                <tr>
                                    <td class="px-4 py-2 font-medium bg-gray-50">{{ $key }}</td>
                                    <td class="px-4 py-2 text-green-600 bg-green-50">
                                        {{ is_array($value) ? json_encode($value) : $value }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <pre class="bg-gray-100 p-4 rounded text-xs overflow-auto">{{ json_encode($record->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
        </div>
    @endif
</div>
