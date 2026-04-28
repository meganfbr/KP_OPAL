<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengajuan Perbaikan</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { margin-top: 30px; text-align: right; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8pt; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SURAT PENGAJUAN PERBAIKAN INVENTARIS</h2>
        <p>Sistem Informasi Operasional dan Pelayanan Administrasi Laboratorium (SIOPAL)</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No PC</th>
                <th>Lab</th>
                <th>Komponen & Keterangan</th>
                <th>Prioritas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->tanggal_pengajuan->format('d/m/Y') }}</td>
                    <td>{{ $row->no_pc }}</td>
                    <td>{{ $row->ruang_lab }}</td>
                    <td>
                        @foreach(collect($row->komponen_rusak) as $k)
                            <strong>{{ is_array($k) ? $k['komponen'] : $k }}</strong>: 
                            {{ (is_array($k) && !empty($k['keterangan'])) ? $k['keterangan'] : '-' }}<br>
                        @endforeach
                    </td>
                    <td>{{ $row->prioritas }}</td>
                    <td>{{ $row->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <br><br><br>
        <p>(...........................................)</p>
        <p>Laboran / Koordinator Lab</p>
    </div>
</body>
</html>
