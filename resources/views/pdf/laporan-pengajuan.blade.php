<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengajuan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .container {
            border: 1px solid black;
            padding: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            border: 1px solid black;
            vertical-align: middle;
            padding: 8px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .title {
            font-size: 13px;
            font-weight: bold;
            margin: 5px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 4px;
        }
        
        .label {
            width: 120px;
            display: inline-block;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .data-table th, .data-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .box {
            border: 1px solid black;
            padding: 8px;
            min-height: 40px;
            margin-bottom: 15px;
        }

        .verification-box {
            border: 1px solid black;
            padding: 8px;
            margin-bottom: 15px;
        }

        .ttd-table {
            width: 100%;
            margin-top: 20px;
        }

        .ttd-table td {
            text-align: center;
            vertical-align: bottom;
            height: 100px;
        }
        
        .footer {
            margin-top: 15px;
            font-size: 9px;
            font-style: italic;
            text-align: right;
            color: #555;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td width="20%" class="center">
                <img src="{{ public_path('image/logo.png') }}" alt="Logo UDINUS" style="max-width: 80px; height: auto;">
            </td>
            <td width="50%" class="center">
                <div class="bold">LABORATORIUM KOMPUTER FIK UDINUS</div>
                <div class="title">
                    FORM PERMINTAAN TINDAKAN PERBAIKAN DAN PENGAJUAN<br>
                    (SOFTWARE DAN HARDWARE)
                </div>
            </td>
            <td width="30%">
                Nomor: {{ $nomor }} <br>
                Revisi: {{ $revisi }} <br>
                Tanggal Berlaku: {{ $tanggal_berlaku }} <br>
                Halaman: 1 dari 1
            </td>
        </tr>
    </table>

    <!-- INFORMASI PENGAJUAN -->
    <table class="info-table">
        <tr>
            <td width="50%">
                <span class="label">Laboratorium</span>: {{ $lab }}<br>
                <span class="label">Pelapor</span>: {{ $pelapor }}<br>
                <span class="label">Tanggal Pengajuan</span>: {{ $tanggal }}
            </td>
            <td width="50%" style="vertical-align: top;">
                <span class="label">Status</span>: Pending<br>
                <span class="label">Prioritas</span>: Sedang
            </td>
        </tr>
    </table>

    <!-- TABEL KERUSAKAN -->
    <div class="section-title">Daftar Kerusakan Inventaris:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No PC</th>
                <th width="20%">Kode PC</th>
                <th width="30%">Komponen (Kondisi)</th>
                <th width="30%">Keterangan Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($tableData) && count($tableData) > 0)
                @foreach($tableData as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $row['no_pc'] }}</td>
                    <td class="center">{{ $row['kode_pc'] }}</td>
                    <td>{!! $row['komponen'] !!}</td>
                    <td>{!! $row['keterangan'] !!}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="center">Tidak ada data kerusakan PC.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- TINDAKAN LANGSUNG -->
    <div class="section-title">Tindakan Langsung / Yang Diajukan:</div>
    <div class="box">
        {!! nl2br(e($tindakan_perbaikan)) !!}
    </div>

    <!-- BAGIAN VERIFIKATOR -->
    <div class="verification-box">
        <div class="section-title" style="margin-top: 0;">Permintaan Tindakan Perbaikan dan Pencegahan (Oleh Admin):</div>
        <br><br><br>
        <div style="border-top: 1px dashed black; margin: 10px 0;"></div>
        
        <div class="section-title">Verifikasi Hasil Tindakan:</div>
        <div>[ &nbsp; ] Diterima &nbsp;&nbsp;&nbsp;&nbsp; [ &nbsp; ] Ditolak</div>
        <br>
        
        <div class="section-title">Evaluasi & Efektifitas Hasil Tindakan:</div>
        <br><br><br>
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd-table">
        <tr>
            <td width="50%">
                Pelapor,<br><br><br><br><br>
                <b>( ........................................ )</b><br>
                Bagian : {{ $lab }}<br>
                Jabatan : {{ $jabatan_pelapor }}
            </td>
            <td width="50%">
                Disetujui,<br><br><br><br><br>
                <b>( ........................................ )</b><br>
                Bagian : ........................................<br>
                Jabatan : ........................................
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Dokumen dihasilkan otomatis oleh Sistem SIOPAL.
    </div>

</div>

</body>
</html>
