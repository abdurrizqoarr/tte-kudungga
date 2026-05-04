<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume Medis</title>
    <style>
        @page {
            /* Margin bottom diperbesar menjadi 180px untuk menyediakan ruang bagi footer BSrE di setiap halaman */
            margin: 60px 30px 180px 30px;
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

        /* Styling khusus untuk Footer BSrE agar muncul di setiap halaman */
        footer {
            position: fixed;
            bottom: -160px; /* Disesuaikan dengan margin bottom @page */
            left: 0;
            right: 0;
            height: 160px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th, td {
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

        /* Override style table untuk elemen di dalam Footer agar tidak memiliki border & background bawaan */
        .layout-table, .layout-table td, .layout-table tr:nth-child(even) {
            border: none;
            background-color: transparent !important;
            padding: 0;
        }

        .bsre-meta, .bsre-meta td, .bsre-meta tr:nth-child(even) {
            border: none;
            background-color: transparent !important;
            padding: 2px 5px;
        }

        .bsre-box {
            border: 1px solid #000;
            width: 100%;
            margin-bottom: 0;
        }

        .bsre-box-inner, .bsre-box-inner td, .bsre-box-inner tr:nth-child(even) {
            border: none;
            background-color: transparent !important;
        }

        .footer-text {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
            line-height: 1.3;
        }
    </style>
</head>

<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('id');
        $tanggalSekarang = Carbon::now()->translatedFormat('d F Y');
    @endphp

    <header>
        <table width="100%" style="border: none; margin-bottom: 10px;" class="layout-table">
            <tr>
                <td width="20%" style="text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('logo/logo_kudungga.png') }}" alt="Logo Kudungga"
                        style="width: 80px; height: 80px; object-fit: contain;">
                </td>

                <td width="60%" style="text-align: center; font-size: 12px;">
                    <h3 style="margin: 0; font-size: 16px;">RSUD Kudungga Sangatta</h3>
                    <p style="margin: 2px 0;">Jl. Soekarno-Hatta, Sangatta Utara, 75681, Kalimantan Timur</p>
                    <p style="margin: 2px 0;">Telp. 0549-2035589</p>
                    <p style="margin: 2px 0;">Email: info@rsudkudungga.com</p>
                </td>

                <td width="20%" style="text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('logo/Logo_Kutai_Timur.png') }}" alt="Logo Kutai Timur"
                        style="width: 80px; height: 60px; object-fit: contain;">
                </td>
            </tr>
        </table>
        <hr>
    </header>

    <footer>
        <table class="layout-table">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    <table class="bsre-meta" style="margin-bottom: 8px;">
                        <tr>
                            <td width="35%">Dikeluarkan di</td>
                            <td width="5%">:</td>
                            <td width="60%">Sangatta</td>
                        </tr>
                        <tr>
                            <td>Pada tanggal</td>
                            <td>:</td>
                            <td>{{ $tanggalSekarang }}</td>
                        </tr>
                    </table>

                    <table class="bsre-box">
                        <tr>
                            <td style="padding: 0; border: none;">
                                <table class="bsre-box-inner" style="margin-bottom: 0; width: 100%;">
                                    <tr>
                                        <td width="25%" style="text-align: center; vertical-align: middle; padding: 10px;">
                                            <img src="{{ public_path('logo/logo_bsre.png') }}" alt="Logo BSrE" style="width: 55px; height: auto;">
                                        </td>
                                        <td width="75%" style="font-size: 9px; line-height: 1.4; padding: 10px 10px 10px 0; vertical-align: middle;">
                                            Ditandatangani secara elektronik oleh:<br>
                                            <b>DOKTER DPJP</b><br><br><br>
                                            <b>{{ $resume['dokter_dpjb'] }}</b>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer-text">
            Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik<br>
            yang telah diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara
        </div>
    </footer>

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
                <td width="30%">Alasan Masuk</td>
                <td style="white-space: pre-line;">{{ $resume['alasan'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Keluhan Utama</td>
                <td style="white-space: pre-line;">{{ $resume['keluhan_utama'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Pemeriksaan Fisik</td>
                <td style="white-space: pre-line;">{{ $resume['pemeriksaan_fisik'] ?: '-' }}</td>
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
                <td width="30%">Diagnosa Awal</td>
                <td>{{ $resume['diagnosa_awal'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Diagnosa Utama</td>
                <td>{{ $resume['diagnosa_utama'] }} ({{ $resume['kd_diagnosa_utama'] ?: '-' }})</td>
            </tr>
            <tr>
                <td>Diagnosa Sekunder</td>
                <td>
                    {{ $resume['diagnosa_sekunder'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder2'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder3'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder4'] ?: '-' }}
                </td>
            </tr>
        </table>

        <div class="section-title">Prosedur & Tindakan</div>
        <table>
            <tr>
                <td width="30%">Prosedur Utama</td>
                <td>{{ $resume['prosedur_utama'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Prosedur Sekunder</td>
                <td>
                    {{ $resume['prosedur_sekunder'] ?: '-' }}<br>
                    {{ $resume['prosedur_sekunder2'] ?: '-' }}<br>
                    {{ $resume['prosedur_sekunder3'] ?: '-' }}
                </td>
            </tr>
            <tr>
                <td>Tindakan / Operasi</td>
                <td>{{ $resume['tindakan_dan_operasi'] ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Obat & Terapi</div>
        <table>
            <tr>
                <td width="30%">Obat di RS</td>
                <td style="white-space: pre-line;">{{ $resume['obat_di_rs'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Obat Pulang</td>
                <td style="white-space: pre-line;">{{ $resume['obat_pulang'] ?: '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Kondisi Pulang & Rencana</div>
        <table>
            <tr>
                <td width="30%">Cara Keluar</td>
                <td>{{ $resume['cara_keluar'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Keadaan Pasien</td>
                <td>{{ $resume['keadaan'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Rencana Dilanjutkan</td>
                <td>{{ $resume['dilanjutkan'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>Kontrol</td>
                <td>{{ $resume['kontrol'] ?: '-' }}</td>
            </tr>
        </table>

        </main>
</body>

</html>