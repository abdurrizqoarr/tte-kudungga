<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume Medis Rawat Inap</title>

    <style>
        @page {
            margin: 60px 30px 70px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin-top: 120px;
        }

        /* ================= HEADER ================= */

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            height: 100px;
        }

        /* ================= FOOTER ================= */

        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
        }

        .footer-page {
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        /* ================= TABLE ================= */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 5px 8px;
            vertical-align: top;
        }

        .layout-table,
        .layout-table td,
        .layout-table tr {
            border: none !important;
            background-color: transparent !important;
            padding: 0 !important;
        }

        /* ================= SECTION ================= */

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #00695c;
            margin: 15px 0 5px;
        }

        /* ================= SIGNATURE ================= */

        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .bsre-box {
            border: 1px solid #333;
            width: 100%;
        }

        .bsre-box td {
            border: none !important;
            padding: 8px !important;
        }

        .bsre-logo-footer {
            width: 50px;
        }

        .footer-text {
            text-align: center;
            font-size: 9px;
            margin-top: 10px;
            color: #666;
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

    <!-- ================= HEADER ================= -->

    <header>

        <table class="layout-table">
            <tr>

                <td width="20%" style="text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('logo/logo_kudungga.png') }}"
                        style="width: 70px;">
                </td>

                <td width="60%" style="text-align: center;">

                    <h3 style="margin: 0; font-size: 15px;">
                        RSUD Kudungga Sangatta
                    </h3>

                    <p style="margin: 2px 0;">
                        Jl. Soekarno-Hatta, Sangatta Utara, 75681, Kalimantan Timur
                    </p>

                    <p style="margin: 2px 0;">
                        Telp. 0549-2035589 | Email: info@rsudkudungga.com
                    </p>

                </td>

                <td width="20%" style="text-align: center; vertical-align: middle;">
                    <img src="{{ public_path('logo/Logo_Kutai_Timur.png') }}"
                        style="width: 70px;">
                </td>

            </tr>
        </table>

        <hr>

    </header>

    <!-- ================= FOOTER ================= -->

    <footer>

        <div class="footer-page">
            Resume Medis Rawat Inap - RSUD Kudungga Sangatta
        </div>

    </footer>

    <!-- ================= MAIN CONTENT ================= -->

    <main>

        <!-- INFORMASI PASIEN -->

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

        <!-- KELUHAN -->

        <div class="section-title">Keluhan & Pemeriksaan</div>

        <table>

            <tr>
                <td width="30%">Alasan Masuk</td>
                <td style="white-space: pre-line;">
                    {{ $resume['alasan'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Keluhan Utama</td>
                <td style="white-space: pre-line;">
                    {{ $resume['keluhan_utama'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Pemeriksaan Fisik</td>
                <td style="white-space: pre-line;">
                    {{ $resume['pemeriksaan_fisik'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Jalannya Penyakit</td>
                <td style="white-space: pre-line;">
                    {{ $resume['jalannya_penyakit'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Pemeriksaan Penunjang</td>
                <td style="white-space: pre-line;">
                    {{ $resume['pemeriksaan_penunjang'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Hasil Laborat</td>
                <td style="white-space: pre-line;">
                    {{ $resume['hasil_laborat'] ?: '-' }}
                </td>
            </tr>

        </table>

        <!-- DIAGNOSA -->

        <div class="section-title">Diagnosa</div>

        <table>

            <tr>
                <td>Diagnosa Awal</td>
                <td>{{ $resume['diagnosa_awal'] ?: '-' }}</td>
            </tr>

            <tr>
                <td>Diagnosa Utama</td>
                <td>
                    {{ $resume['diagnosa_utama'] }}
                    ({{ $resume['kd_diagnosa_utama'] ?: '-' }})
                </td>
            </tr>

            <tr>
                <td width="30%">Diagnosa Sekunder</td>

                <td style="white-space: pre-line;">
                    {{ $resume['diagnosa_sekunder'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder2'] ?: '-' }}<br>
                    {{ $resume['diagnosa_sekunder3'] ?: '-' }}
                </td>
            </tr>

        </table>

        <!-- PROSEDUR -->

        <div class="section-title">Prosedur & Tindakan</div>

        <table>

            <tr>
                <td width="30%">Prosedur Utama</td>
                <td>{{ $resume['prosedur_utama'] ?: '-' }}</td>
            </tr>

            <tr>
                <td>Prosedur Sekunder</td>

                <td style="white-space: pre-line;">
                    {{ $resume['prosedur_sekunder'] ?: '-' }}<br>
                    {{ $resume['prosedur_sekunder2'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Tindakan / Operasi</td>
                <td>{{ $resume['tindakan_dan_operasi'] ?: '-' }}</td>
            </tr>

        </table>

        <!-- OBAT -->

        <div class="section-title">Obat & Terapi</div>

        <table>

            <tr>
                <td width="30%">Obat di RS</td>

                <td style="white-space: pre-line;">
                    {{ $resume['obat_di_rs'] ?: '-' }}
                </td>
            </tr>

            <tr>
                <td>Obat Pulang</td>

                <td style="white-space: pre-line;">
                    {{ $resume['obat_pulang'] ?: '-' }}
                </td>
            </tr>

        </table>

        <!-- KONDISI PULANG -->

        <div class="section-title">Kondisi Pulang</div>

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
                <td>Rencana Lanjut</td>
                <td>{{ $resume['dilanjutkan'] ?: '-' }}</td>
            </tr>

            <tr>
                <td>Kontrol</td>
                <td>{{ $resume['kontrol'] ?: '-' }}</td>
            </tr>

        </table>

        <!-- ================= SIGNATURE ================= -->

        <div class="signature-section">

            <table class="layout-table">

                <tr>

                    <td width="55%"></td>

                    <td width="45%">

                        <div style="
                            font-size: 10px;
                            margin-bottom: 4px;
                            padding-left: 5px;
                        ">
                            Sangatta, {{ $tanggalSekarang }}
                        </div>

                        <table class="bsre-box">

                            <tr>

                                <td width="30%"
                                    style="
                                        text-align: center;
                                        vertical-align: middle;
                                        border-right: 1px solid #ccc !important;
                                    ">

                                    <img src="data:image/png;base64,{{ $resume['qr_code_base64'] }}"
                                        style="width: 120px; height: 120px;">

                                </td>

                                <td width="70%"
                                    style="
                                        font-size: 9px;
                                        vertical-align: middle;
                                        padding-left: 10px !important;
                                    ">

                                    Ditandatangani secara elektronik oleh:<br>

                                    <strong style="font-size: 10px;">
                                        DOKTER DPJP
                                    </strong>

                                    <br><br><br>

                                    <strong style="font-size: 10px;">
                                        {{ $resume['dokter_dpjb'] }}
                                    </strong>

                                </td>

                            </tr>

                        </table>

                        <div style="text-align: right; margin-top: 10px;">

                            <img src="{{ public_path('logo/logo-bsre.png') }}"
                                class="bsre-logo-footer"
                                alt="Logo BSrE">

                        </div>

                    </td>

                </tr>

            </table>

            <div class="footer-text">
                Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik<br>
                yang telah diterbitkan oleh Balai Besar Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara
            </div>

        </div>

    </main>

</body>

</html>