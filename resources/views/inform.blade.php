<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Dokumen TTE</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F7F9FB;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #ffffff;
            width: 90%;
            max-width: 600px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        h1 {
            color: #3B4A58;
            font-size: 24px;
            margin-bottom: 15px;
        }

        p {
            color: #546071;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .info-box {
            background: #F1F5FB;
            border-radius: 10px;
            padding: 16px;
            margin: 20px 0;
            text-align: left;
            font-size: 15px;
            color: #3B4A58;
        }

        .info-box strong {
            display: inline-block;
            width: 140px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            background-color: #4e85d8;
            color: white;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: #6C9DE3;
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }

            h1 {
                font-size: 20px;
            }

            p {
                font-size: 14px;
            }

            .info-box {
                font-size: 14px;
            }

            .info-box strong {
                width: 120px;
            }

            .btn {
                width: 100%;
                padding: 14px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Validasi Dokumen Tanda Tangan Elektronik</h1>

        <p>
            Dokumen ini telah berhasil ditandatangani menggunakan<br>
            <strong>Tanda Tangan Elektronik Standar BSrE (BSSN).</strong>
        </p>

        <div class="info-box">
            <p>
                <strong>Nama Dokter</strong>: {{ $data->dokter_dpjb }}
            </p>
            <p>
                <strong>Tanggal TTE</strong>:
                {{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y H:i') }} WIB
            </p>
        </div>

        <p>
            Untuk memastikan keabsahan dan integritas dokumen, silakan lakukan proses
            <em>validasi dokumen</em> melalui portal resmi berikut.
        </p>

        <a href="https://tte.kutaitimurkab.go.id/" target="_blank" class="btn">
            Validasi Dokumen
        </a>

        <p style="margin-top:25px; font-size:14px; color:#7B8794;">
            Pastikan koneksi internet stabil dan gunakan browser terbaru untuk hasil terbaik.
        </p>
    </div>
</body>

</html>
