<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
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
            padding: 4px 2px;
            text-align: center;
            word-wrap: break-word;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .signature {
            float: right;
            width: 200px;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekapitulasi Inventaris PC</h1>
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
                <th width="3%">No</th>
                <th width="7%">No PC</th>
                <th width="8%">Monitor</th>
                <th width="8%">RAM</th>
                <th width="8%">Proc</th>
                <th width="8%">Mobo</th>
                <th width="8%">HDD</th>
                <th width="8%">VGA</th>
                <th width="7%">DVD</th>
                <th width="7%">Kbd</th>
                <th width="7%">Mouse</th>
                <th width="10%">Lokasi</th>
                <th width="11%">Kondisi PC</th>
            </tr>
        </thead>
        <tbody>
            @php
                $getKon = function($details, $komponen) {
                    $found = $details?->firstWhere('komponen', $komponen);
                    if (!$found) return '-';
                    
                    $out = '<div>' . ($found->kondisi ?: '-') . '</div>';
                    if (!empty($found->catatan_kondisi)) {
                        $out .= '<div style="font-size: 7px; color: #666; font-style: italic;">(' . $found->catatan_kondisi . ')</div>';
                    }
                    return $out;
                };
            @endphp
            @foreach($pcs as $index => $pc)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pc->no_pc }}</td>
                    <td>{!! $getKon($pc->spec?->details, 'Monitor') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'RAM') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'Processor') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'Motherboard') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'Hardisk') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'VGA') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'DVD') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'Keyboard') !!}</td>
                    <td>{!! $getKon($pc->spec?->details, 'Mouse') !!}</td>
                    <td>{{ $pc->lokasi }}</td>
                    <td>{{ $pc->kondisi }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

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
            @forelse($nonpcs ?? [] as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td align="left">{{ $item->nama_barang }}</td>
                    <td align="left">{{ $item->merk_model }}</td>
                    <td>{{ $item->jumlah }}</td>
                    <td>{{ $item->kondisi }}</td>
                    <td align="left">{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada data inventaris non-PC</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Semarang, {{ date('d F Y') }}</p>
            <p>Laboran,</p>
            <br><br><br>
            <p><strong>( ____________________ )</strong></p>
        </div>
    </div>
</body>
</html>
