<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px; /* Slightly smaller to fit many columns */
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .data-table th, .data-table td {
            border: 1px solid #999;
            padding: 3px 2px;
            text-align: center;
            word-wrap: break-word;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8px;
        }
        .footer {
            margin-top: 20px;
            width: 100%;
            font-size: 10px;
        }
        .legend {
            float: left;
            width: 60%;
        }
        .legend ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .legend li {
            margin-bottom: 3px;
        }
        .signature {
            float: right;
            width: 200px;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
        
        .c-baik { color: green; font-weight: bold; }
        .c-kurang { color: #d4a017; font-weight: bold; } /* Darker yellow/orange */
        .c-rusak { color: red; font-weight: bold; }
        .c-tidakada { color: gray; }
    </style>
</head>
<body>
    @php
        $expectedComponents = ['Motherboard', 'Processor', 'RAM', 'Hardisk', 'VGA', 'DVD', 'Keyboard', 'Mouse', 'Monitor'];
    @endphp

    <div class="header">
        <h1>Rekap Inventaris PC</h1>
        <p>{{ $periode->laboratorium?->ruang ?? 'Semua Laboratorium' }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Periode:</strong></td>
            <td>{{ $periode->nama_periode }}</td>
            <td width="15%" align="right"><strong>Tanggal Cetak:</strong></td>
            <td width="20%" align="right">{{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" width="4%">No PC</th>
                @foreach($expectedComponents as $comp)
                    <th colspan="2">{{ $comp === 'Hardisk' ? 'SSD/Hardisk' : $comp }}</th>
                @endforeach
                <th rowspan="2" width="5%">Kondisi PC</th>
            </tr>
            <tr>
                @foreach($expectedComponents as $comp)
                    <th width="4%">Kondisi</th>
                    <th width="5.5%">Keterangan</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($pcs as $index => $pc)
                @php
                    $details = collect($pc->spec?->details ?? [])->keyBy('komponen');
                    // Keterangan per-PC (override spec yang shared) — key by komponen
                    $pcNotes = collect($pc->notes ?? [])->keyBy('komponen');
                @endphp
                <tr>
                    <td>{{ $pc->no_pc }}</td>
                    @foreach($expectedComponents as $comp)
                        @php
                            $detail = $details->get($comp);
                            $kondisi = $detail ? $detail->kondisi : '-';
                            if ($kondisi === '' || $kondisi === null) $kondisi = '-';

                            // Prioritaskan catatan dari tabel pc_notes (per-PC).
                            // Fallback ke catatan_kondisi di spec_detail hanya jika tidak ada di pc_notes.
                            $pcNote = $pcNotes->get($comp);
                            $keterangan = $pcNote
                                ? ($pcNote->catatan_kondisi ?? '')
                                : ($detail ? ($detail->catatan_kondisi ?? '') : '');
                            
                            $colorClass = match($kondisi) {
                                'Baik' => 'c-baik',
                                'Kurang Baik' => 'c-kurang',
                                'Rusak' => 'c-rusak',
                                'Tidak Ada' => 'c-tidakada',
                                default => 'c-tidakada',
                            };
                        @endphp
                        <td class="{{ $colorClass }}">{{ $kondisi }}</td>
                        <td style="font-size: 7px; text-align: left;">{{ $keterangan ?: '-' }}</td>
                    @endforeach
                    @php
                        $pcKondisiClass = match($pc->kondisi) {
                            'Baik' => 'c-baik',
                            'Kurang Baik' => 'c-kurang',
                            'Rusak' => 'c-rusak',
                            default => 'c-tidakada',
                        };
                    @endphp
                    <td class="{{ $pcKondisiClass }}">{{ $pc->kondisi ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="legend">
            <strong>Keterangan Kondisi:</strong>
            <ul>
                <li><span class="c-baik">Baik</span> = Berfungsi normal</li>
                <li><span class="c-kurang">Kurang Baik</span> = Masih dapat digunakan namun terdapat masalah</li>
                <li><span class="c-rusak">Rusak</span> = Tidak dapat digunakan</li>
                <li><span class="c-tidakada">Tidak Ada</span> = Komponen tidak tersedia</li>
            </ul>
        </div>
        <div class="signature">
            <p>Semarang, {{ date('d F Y') }}</p>
            <p>Laboran,</p>
            <br><br><br>
            <p><strong>( ____________________ )</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>

    @if(isset($nonpcs) && count($nonpcs) > 0)
        <div class="page-break"></div>

        <div class="header">
            <h1>Rekapitulasi Inventaris Non-PC</h1>
            <p>{{ $periode->laboratorium?->ruang ?? 'Semua Laboratorium' }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="15%"><strong>Periode:</strong></td>
                <td>{{ $periode->nama_periode }}</td>
                <td width="15%" align="right"><strong>Tanggal Cetak:</strong></td>
                <td width="20%" align="right">{{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Nama Barang</th>
                    <th width="25%">Merk/Model</th>
                    <th width="10%">Jumlah</th>
                    <th width="15%">Kondisi</th>
                    <th width="15%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nonpcs as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td align="left">{{ $item->nama_barang }}</td>
                        <td align="left">{{ $item->merk_model }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>{{ $item->kondisi }}</td>
                        <td align="left">{{ $item->keterangan ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
