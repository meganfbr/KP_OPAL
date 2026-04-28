<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengajuan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .container {
            border: 1px solid black;
            padding: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: 1px solid black;
            vertical-align: top;
            padding: 5px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
        }

        .section {
            border: 1px solid black;
            padding: 8px;
        }

        .label {
            width: 200px;
            display: inline-block;
        }

        .ttd {
            width: 100%;
            margin-top: 40px;
        }

        .ttd td {
            text-align: center;
            padding-top: 40px;
        }

        .box {
            border: 1px solid black;
            padding: 8px;
            min-height: 60px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <!-- LOGO -->
            <td width="20%" class="center">
                <!-- We can replace this with an actual logo if available, or text -->
                <div style="font-weight: bold; margin-top: 15px; font-size: 18px;">UDINUS</div>
            </td>

            <!-- JUDUL -->
            <td width="50%" class="center">
                <div class="bold">LABORATORIUM KOMPUTER FIK UDINUS</div>
                <div class="title">
                    PERMINTAAN TINDAKAN PERBAIKAN DAN PENCEGAHAN
                </div>
                <div>(SOFTWARE DAN HARDWARE)</div>
            </td>

            <!-- INFO KANAN -->
            <td width="30%">
                Nomor: {{ $nomor }} <br>
                Revisi: {{ $revisi }} <br>
                Tanggal Berlaku: {{ $tanggal_berlaku }} <br>
                Halaman: 1
            </td>
        </tr>
    </table>

    <br>

    <!-- INFO UTAMA -->
    <div class="section">
        <div><span class="label">Bentuk Ketidaksesuaian</span>: {{ $ketidaksesuaian }}</div>
        <div><span class="label">Lokasi</span>: {{ $lab }}</div>
        <div><span class="label">Tanggal Kejadian</span>: {{ $tanggal }}</div>
        <div><span class="label">Tanggal Laporan</span>: {{ $tanggal }}</div>
    </div>

    <br>

    <!-- URAIAN -->
    <div class="section">
        <b>Hasil / Uraian Pengamatan Ketidaksesuaian:</b>
        <div class="box">
            {!! nl2br(e($uraian)) !!}
        </div>
    </div>

    <br>

    <!-- TINDAKAN LANGSUNG -->
    <div class="section">
        <b>Tindakan Langsung:</b>
        <div class="box">
            {!! nl2br(e($tindakan_langsung)) !!}
        </div>
    </div>

    <br>

    <!-- PERBAIKAN -->
    <div class="section">
        <b>Permintaan Tindakan Perbaikan dan Pencegahan:</b>
        <div class="box">
            {!! nl2br(e($tindakan_perbaikan)) !!}
        </div>
    </div>

    <!-- TTD -->
    <table class="ttd">
        <tr>
            <td>
                Pelapor,<br><br><br><br>
                ( {{ $pelapor }} )<br>
                Jabatan: {{ $jabatan_pelapor }}
            </td>

            <td>
                Disetujui,<br><br><br><br>
                ( {{ $admin }} )<br>
                Jabatan: {{ $jabatan_admin }}
            </td>
        </tr>
    </table>

</div>

</body>
</html>
