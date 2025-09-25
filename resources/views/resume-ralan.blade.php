<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume Medis</title>
    <style>
        @page {
            margin: 60px 30px 80px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin-top: 120px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            height: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 6px 8px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #00695c;
            margin: 18px 0 8px;
        }

        .ttd {
            margin-top: 60px;
            text-align: right;
        }

        .ttd p {
            margin: 3px 0;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <table width="100%" style="border: none; margin-bottom: 10px;">
            <tr>
                <td width="20%" style="text-align: center; border: none; vertical-align: middle;">
                    <img src="{{ public_path('logo/logo_kudungga.png') }}" alt="Logo Kudungga"
                        style="width: 80px; height: 80px; object-fit: contain;">
                </td>

                <td width="60%" style="text-align: center; border: none; font-size: 12px;">
                    <h3 style="margin: 0; font-size: 16px;">RSUD Kudungga Sangatta</h3>
                    <p style="margin: 2px 0;">Jl. Soekarno-Hatta, Sangatta Utara, 75681, Kalimantan Timur</p>
                    <p style="margin: 2px 0;">Telp. 0549-2035589</p>
                    <p style="margin: 2px 0;">Email: info@rsudkudungga.com</p>
                </td>

                <td width="20%" style="text-align: center; border: none; vertical-align: middle;">
                    <img src="{{ public_path('logo/Logo_Kutai_Timur.png') }}" alt="Logo Kutai Timur"
                        style="width: 80px; height: 60px; object-fit: contain;">
                </td>
            </tr>
        </table>
        <hr>
    </header>

    <!-- Konten -->
    <main>
        <div class="section-title">Informasi Pasien</div>
        <table>
            <tr>
                <td width="30%">No. Rawat</td>
                <td>{{ $resume['no_rawat'] }}</td>
            </tr>
            <tr>
                <td>No. RM</td>
                <td>{{ $resume['no_rkm_medis'] }}</td>
            </tr>
            <tr>
                <td>Nama Pasien</td>
                <td>{{ $resume['nm_pasien'] }}</td>
            </tr>
            <tr>
                <td>Dokter DPJP</td>
                <td>{{ $resume['dokter_dpjb'] }}</td>
            </tr>
            <tr>
                <td>Poli</td>
                <td>{{ $resume['nm_poli'] }}</td>
            </tr>
            <tr>
                <td>Tanggal Registrasi</td>
                <td>{{ $resume['tgl_registrasi'] }}</td>
            </tr>
        </table>

        <div class="section-title">Keluhan & Pemeriksaan</div>
        <table>
            <tr>
                <td width="30%">Keluhan Utama</td>
                <td style="white-space: pre-line;">{{ $resume['keluhan_utama'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Jalannya Penyakit</td>
                <td style="white-space: pre-line;">{{ $resume['jalannya_penyakit'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Pemeriksaan Penunjang</td>
                <td style="white-space: pre-line;">{{ $resume['pemeriksaan_penunjang'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Hasil Laborat</td>
                <td style="white-space: pre-line;">{{ $resume['hasil_laborat'] ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Diagnosa</div>
        <table>
            <tr>
                <td width="30%">Diagnosa Utama</td>
                <td style="white-space: pre-line;">{{ $resume['diagnosa_utama'] }}
                    ({{ $resume['kd_diagnosa_utama'] ?: '-' }})</td>
            </tr>
            <tr>
                <td>Diagnosa Sekunder</td>
                <td style="white-space: pre-line;">
                    {{ $resume['diagnosa_sekunder'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder2'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder3'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder4'] ?: '-' }}
                </td>
            </tr>
        </table>

        <div class="section-title">Prosedur</div>
        <table>
            <tr>
                <td width="30%">Prosedur Utama</td>
                <td style="white-space: pre-line;">{{ $resume['prosedur_utama'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Prosedur Sekunder</td>
                <td style="white-space: pre-line;">
                    {{ $resume['prosedur_sekunder'] ?: '-' }}<br>
                    {{ $resume['prosedur_sekunder2'] ?: '-' }}<br>
                    {{ $resume['prosedur_sekunder3'] ?: '-' }}
                </td>
            </tr>
        </table>

        <div class="section-title">Kondisi Pulang</div>
        <table>
            <tr>
                <td width="30%">Kondisi Pulang</td>
                <td style="white-space: pre-line;">{{ $resume['kondisi_pulang'] ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Obat Pulang</div>
        <table>
            <tr>
                <td width="30%">Obat Pulang</td>
                <td style="white-space: pre-line;">{{ $resume['obat_pulang'] ?: '-' }}</td>
            </tr>
        </table>

        @php
            use Carbon\Carbon;
            Carbon::setLocale('id');
            $tanggalSekarang = Carbon::now()->translatedFormat('d F Y');
        @endphp

        <div class="ttd" style="text-align: center; margin-top: 60px;">
            <p>Sangatta, {{ $tanggalSekarang }}</p>
            <img src="{{ public_path('logo/qrcode.png') }}" alt="qr"
                style="width: 120px; height: 120px; object-fit: contain; margin-top: 3px; margin-bottom: 3px;">
            <p><strong>{{ $resume['dokter_dpjb'] }}</strong></p>
        </div>

    </main>
</body>

</html>
