<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
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
            padding: 6px 4px;
            text-align: left;
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
                <th width="5%">No</th>
                <th width="10%">No PC</th>
                <th width="15%">Kode Spek</th>
                <th width="50%">Spesifikasi Detail</th>
                <th width="10%">Lokasi</th>
                <th width="10%">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pcs as $index => $pc)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td align="center">{{ $pc->no_pc }}</td>
                    <td align="center">{{ $pc->spec?->kode_spek ?? '-' }}</td>
                    <td>
                        @if($pc->spec && $pc->spec->details)
                            @foreach($pc->spec->details as $detail)
                                <div>• {{ $detail->komponen }}: {{ $detail->detail }} ({{ $detail->kondisi }})</div>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td align="center">{{ $pc->lokasi }}</td>
                    <td align="center">{{ $pc->kondisi }}</td>
                </tr>
            @endforeach
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
