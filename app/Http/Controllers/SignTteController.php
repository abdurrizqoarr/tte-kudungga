<?php

namespace App\Http\Controllers;

use App\Models\ResumeRalanLog;
use App\Models\ResumeRanapLog;
use App\Models\RiwayatResumeRalan;
use App\Models\RiwayatResumeRamap;
use App\Models\SignDokumen;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SignTteController extends Controller
{
    public function maskSensitive(?string $val, $start = 2, $end = 2)
    {
        if (!$val) return null;
        $len = mb_strlen($val);
        if ($len <= ($start + $end)) return str_repeat('*', $len);
        return mb_substr($val, 0, $start) . str_repeat('*', $len - ($start + $end)) . mb_substr($val, -$end);
    }

    public function signDokumenWithQr(Request $request)
    {
        try {
            // Validasi request dengan custom message
            $validator = Validator::make($request->all(), [
                'signed_file' => 'required|file|mimes:pdf|max:5120',
                'passphrase'  => 'required|string',
                'nik'         => 'required|string',
                'page'        => 'required|integer|min:1',
                'xAxis'       => 'required|numeric|min:0',
                'yAxis'       => 'required|numeric|min:0',
            ], [
                'signed_file.required' => 'File PDF wajib diunggah.',
                'signed_file.mimes'    => 'File harus berformat PDF.',
                'signed_file.max'      => 'Ukuran file maksimal 5MB.',
                'passphrase.required'  => 'Passphrase wajib diisi.',
                'nik.required'         => 'NIK wajib diisi.',
                'page.required'        => 'Nomor halaman wajib diisi.',
                'page.integer'         => 'Nomor halaman harus berupa angka.',
                'xAxis.required'       => 'Posisi X wajib diisi.',
                'yAxis.required'       => 'Posisi Y wajib diisi.',
            ]);

            if ($validator->fails()) {
                Log::warning("Validasi gagal saat sign dokumen dengan QR", [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Baca file PDF
            $file = $request->file('signed_file');

            // Payload untuk server utama
            $payload = [
                'nik'        => $request->input('nik'),
                'passphrase' => $request->input('passphrase'),
                'tampilan'   => "visible",
                'linkQR'     => "http://tte.kutaitimurkab.go.id",
                'page'       => $request->input('page'),
                'xAxis'      => $request->input('xAxis'),
                'yAxis'      => $request->input('yAxis'),
                'width'      => 50,
                'height'     => 50,
            ];

            Log::info("Mengirim request sign dokumen dengan QR", [
                'data'    => $payload,
            ]);

            // Panggil API eksternal
            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->attach(
                    'file',                 // nama field file sesuai yang diminta API
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )->post(env('BASE_URL_BSRE') . '/sign/pdf', $payload);

            if ($response->successful()) {
                Log::info("Dokumen berhasil ditandatangani dengan QR", [
                    'nik' => $payload['nik']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil ditandatangani dengan QR',
                    'data'    => $response->headers()
                ]);
            }

            Log::error("Gagal menandatangani dokumen dengan QR", [
                'status'  => $response->status(),
                'error'   => $response->json()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandatangani dokumen dengan QR',
                'error'   => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Exception saat sign dokumen dengan QR", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal pada server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function resumeRalan(Request $request)
    {
        // ambil input
        $resume     = $request->input('resume');
        $nik        = $request->input('nik');
        $passphrase = $request->input('passphrase');

        $maskNik = $this->maskSensitive($nik);

        ResumeRalanLog::create([
            'action' => "START_SIGN",
            'description' => $maskNik . " Memulai Sign Dokumen Resume Ralan Pasien" . $request->input('resume.no_rawat'),
            'user_id' => $request->ip(),
        ]);

        // validasi resume
        if (!$resume) {
            ResumeRalanLog::create([
                'action' => 'VALIDATION_FAILED',
                'description' => 'Resume kosong / tidak ditemukan. Resume present: ' . ($request->has('resume') ? 'YA' : 'TIDAK'),
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Data resume tidak ditemukan'
            ], 400);
        }

        // validasi credential
        if (!$nik || !$passphrase) {
            ResumeRalanLog::create([
                'action' => 'VALIDATION_FAILED',
                'description' => 'Credential signing tidak lengkap. '
                    . 'NIK present: ' . ($nik ? 'YA' : 'TIDAK')
                    . ', Passphrase present: ' . ($passphrase ? 'YA' : 'TIDAK'),
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Nik atau passphrase tidak boleh kosong'
            ], 400);
        }

        // generate PDF
        try {
            ResumeRalanLog::create([
                'action' => 'START_GENERATE_PDF',
                'description' => $request->input('resume.no_rawat') . " Mulai Generate PDF dan QR",
                'user_id' => $request->ip(),
            ]);

            $riwayatDokumen = RiwayatResumeRalan::create([
                "nama_penanda_tangan" => $request->input('resume.dokter_dpjb')
            ]);

            $linkqr = env('APP_URL') . '/verifikasi-dokumen/' . $riwayatDokumen->id;

            //Generate QR
            $qrPng = QrCode::format('png')
                ->size(100)
                ->errorCorrection('H')
                ->generate($linkqr);

            //Ubah QR Ke Base 64
            $qrCodeBase64 = base64_encode($qrPng);

            //Masukan Base64 Ke Attribute View
            $resume['qr_code_base64'] = $qrCodeBase64;

            //Load View PDF
            $pdf = PDF::loadView('resume-ralan', compact('resume'))->setPaper('A4');

            //Hasil Akhir PDF
            $pdfContent = $pdf->output();

            ResumeRalanLog::create([
                'action' => 'GENERATE_PDF_SUCCESS',
                'description' => $request->input('resume.no_rawat') . " Berhasil Generate PDF",
                'user_id' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            ResumeRalanLog::create([
                'action' => 'GENERATE_PDF_FAILED',
                'description' => $request->input('resume.no_rawat') . " Gagal Generate PDF [" . $e->getMessage() . "]",
                'user_id' => $request->ip(),
            ]);


            return response()->json([
                'error' => 'Gagal generate PDF',
                'message' => $e->getMessage(),
            ], 500);
        }

        // siapkan filename
        $filename = 'resume_ralan_' . Carbon::now()->timestamp . '.pdf';

        // URL API BSRE
        $apiUrl = rtrim(env('BASE_URL_BSRE', ''), '/') . '/sign/pdf';

        ResumeRalanLog::create([
            'action' => 'START_SIGN_BSRE',
            'description' => $request->input('resume.no_rawat') . " Akan dikirim ke BSRE",
            'user_id' => $request->ip(),
        ]);

        try {
            //Kirim KE BSRE
            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->timeout(60)          // timeout request
                ->attach('file', $pdfContent, $filename)
                ->post($apiUrl, [
                    'nik' => $nik,
                    'passphrase' => $passphrase,
                    'tampilan' => 'invisible',
                ]);

            // log status dan beberapa header penting (tanpa mengeluarkan header sensitif)
            $statusCode     = $response->status();
            $contentType    = $response->header('Content-Type');
            $contentLength  = $response->header('Content-Length');

            // simpan log ke database
            ResumeRalanLog::create([
                'action' => $statusCode === 200 ? 'SIGN_BSRE_RESPONSE_SUCCESS' : 'SIGN_BSRE_RESPONSE_FAILED',
                'description' =>
                'Response signing API diterima. '
                    . 'No Rawat: ' . $request->input('resume.no_rawat', '-') . '. '
                    . 'HTTP Status: ' . $statusCode . '. '
                    . 'Content-Type: ' . ($contentType ?? '-') . '. '
                    . 'Content-Length: ' . ($contentLength ?? '-'),
                'user_id' => $request->ip(),
            ]);

            // sukses
            if ($response->successful()) {
                return response()->json([
                    'message' => 'Resume berhasil DI SIGN',
                    'api_response' => $response->headers(),
                ], 200);
            }

            // jika HTTP error (4xx/5xx) dari API
            $respBody = $response->body();
            $bodyPreview = is_string($respBody)
                ? substr($respBody, 0, 2000)
                : json_encode($respBody, JSON_UNESCAPED_UNICODE);

            ResumeRalanLog::create([
                'action' => 'SIGN_BSRE_ERROR',
                'description' =>
                'Signing API mengembalikan error. '
                    . 'No Rawat: ' . $request->input('resume.no_rawat', '-') . '. '
                    . 'HTTP Status: ' . $statusCode . '. '
                    . 'Response Preview: ' . $bodyPreview,
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Signing API error',
                'status' => $response->status(),
                'body' => $respBody,
            ], $response->status() ?: 500);
        } catch (\Throwable $e) {
            // error koneksi / timeout / exception lain
            ResumeRalanLog::create([
                'action' => 'START_SIGN_ERROR_SERVER',
                'description' => $request->input('resume.no_rawat') . " Gagal SIGN [ " . "excrption : " . $e->getMessage() . '; trace : ' . $e->getTraceAsString() . ' ]',
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Koneksi ke signing API gagal',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function resumeRanap(Request $request)
    {
        $resume     = $request->input('resume');
        $nik        = $request->input('nik');
        $passphrase = $request->input('passphrase');

        $maskNik = $this->maskSensitive($nik);

        ResumeRanapLog::create([
            'action' => "START_SIGN",
            'description' => $maskNik . " Memulai Sign Dokumen Resume Ralan Pasien" . $request->input('resume.no_rawat'),
            'user_id' => $request->ip(),
        ]);

        // validasi resume
        if (!$resume) {
            ResumeRanapLog::create([
                'action' => 'VALIDATION_FAILED',
                'description' => 'Resume kosong / tidak ditemukan. Resume present: ' . ($request->has('resume') ? 'YA' : 'TIDAK'),
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Data resume tidak ditemukan'
            ], 400);
        }

        // validasi credential
        if (!$nik || !$passphrase) {
            ResumeRanapLog::create([
                'action' => 'VALIDATION_FAILED',
                'description' => 'Credential signing tidak lengkap. '
                    . 'NIK present: ' . ($nik ? 'YA' : 'TIDAK')
                    . ', Passphrase present: ' . ($passphrase ? 'YA' : 'TIDAK'),
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Nik atau passphrase tidak boleh kosong'
            ], 400);
        }

        // generate PDF
        try {
            ResumeRanapLog::create([
                'action' => 'START_GENERATE_PDF',
                'description' => $request->input('resume.no_rawat') . " Mulai Generate PDF dan QR",
                'user_id' => $request->ip(),
            ]);

            $riwayatDokumen = RiwayatResumeRamap::create([
                "nama_penanda_tangan" => $request->input('resume.dokter_dpjb')
            ]);

            $linkqr = env('APP_URL') . '/verifikasi-dokumen/' . $riwayatDokumen->id;

            //Generate QR
            $qrPng = QrCode::format('png')
                ->size(100)
                ->errorCorrection('H')
                ->generate($linkqr);

            //Ubah QR Ke Base 64
            $qrCodeBase64 = base64_encode($qrPng);

            //Masukan Base64 Ke Attribute View
            $resume['qr_code_base64'] = $qrCodeBase64;

            //Load View PDF
            $pdf = PDF::loadView('resume-ralan', compact('resume'))->setPaper('A4');

            //Hasil Akhir PDF
            $pdfContent = $pdf->output();

            ResumeRanapLog::create([
                'action' => 'GENERATE_PDF_SUCCESS',
                'description' => $request->input('resume.no_rawat') . " Berhasil Generate PDF",
                'user_id' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            ResumeRanapLog::create([
                'action' => 'GENERATE_PDF_FAILED',
                'description' => $request->input('resume.no_rawat') . " Gagal Generate PDF [" . $e->getMessage() . "]",
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Gagal generate PDF',
                'message' => $e->getMessage(),
            ], 500);
        }

        // siapkan filename
        $filename = 'resume_ranap_' . Carbon::now()->timestamp . '.pdf';

        // URL API BSRE
        $apiUrl = rtrim(env('BASE_URL_BSRE', ''), '/') . '/sign/pdf';

        ResumeRanapLog::create([
            'action' => 'START_SIGN_BSRE',
            'description' => $request->input('resume.no_rawat') . " Akan dikirim ke BSRE",
            'user_id' => $request->ip(),
        ]);

        try {
            //Kirim Ke BSRE
            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->timeout(180)
                ->attach('file', $pdfContent, $filename)
                ->post($apiUrl, [
                    'nik' => $nik,
                    'passphrase' => $passphrase,
                    'tampilan' => 'invisible',
                ]);

            // log status dan beberapa header penting (tanpa mengeluarkan header sensitif)
            $statusCode     = $response->status();
            $contentType    = $response->header('Content-Type');
            $contentLength  = $response->header('Content-Length');

            // simpan log ke database
            ResumeRalanLog::create([
                'action' => $statusCode === 200 ? 'SIGN_BSRE_RESPONSE_SUCCESS' : 'SIGN_BSRE_RESPONSE_FAILED',
                'description' =>
                'Response signing API diterima. '
                    . 'No Rawat: ' . $request->input('resume.no_rawat', '-') . '. '
                    . 'HTTP Status: ' . $statusCode . '. '
                    . 'Content-Type: ' . ($contentType ?? '-') . '. '
                    . 'Content-Length: ' . ($contentLength ?? '-'),
                'user_id' => $request->ip(),
            ]);

            // sukses
            if ($response->successful()) {
                return response()->json([
                    'message' => 'Resume berhasil DI SIGN',
                    'api_response' => $response->headers(),
                ], 200);
            }

            // jika HTTP error (4xx/5xx) dari API
            $respBody = $response->body();
            $bodyPreview = is_string($respBody)
                ? substr($respBody, 0, 2000)
                : json_encode($respBody, JSON_UNESCAPED_UNICODE);

            ResumeRalanLog::create([
                'action' => 'SIGN_BSRE_ERROR',
                'description' =>
                'Signing API mengembalikan error. '
                    . 'No Rawat: ' . $request->input('resume.no_rawat', '-') . '. '
                    . 'HTTP Status: ' . $statusCode . '. '
                    . 'Response Preview: ' . $bodyPreview,
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Signing API error',
                'status' => $response->status(),
                'body' => $respBody,
            ], $response->status() ?: 500);
        } catch (\Throwable $e) {
            // error koneksi / timeout / exception lain
            ResumeRalanLog::create([
                'action' => 'START_SIGN_ERROR_SERVER',
                'description' => $request->input('resume.no_rawat') . " Gagal SIGN [ " . "excrption : " . $e->getMessage() . '; trace : ' . $e->getTraceAsString() . ' ]',
                'user_id' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Koneksi ke signing API gagal',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function signDokumenNoQr(Request $request)
    {
        // Validasi request dengan custom message
        $validator = Validator::make($request->all(), [
            'signed_file' => 'required|file|mimes:pdf|max:5120',
            'passphrase'  => 'required|string',
            'nik'         => 'required|string',
        ], [
            'signed_file.required' => 'File PDF wajib diunggah.',
            'signed_file.mimes'    => 'File harus berformat PDF.',
            'signed_file.max'      => 'Ukuran file maksimal 5MB.',
            'passphrase.required'  => 'Passphrase wajib diisi.',
            'nik.required'         => 'NIK wajib diisi.',
        ]);

        if ($validator->fails()) {
            Log::channel('signature_no_qr')->warning("Validasi gagal saat sign dokumen dengan QR", [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Baca file PDF
            $file = $request->file('signed_file');

            // Payload untuk server utama
            $payload = [
                'nik'        => $request->input('nik'),
                'passphrase' => $request->input('passphrase'),
                'tampilan'   => "invisible",
            ];

            Log::info('Payload TTE:', [
                'nik'      => $payload['nik'],
                'tampilan' => $payload['tampilan'],
                'passphrase' => $payload['passphrase'],
            ]);

            Log::channel('signature_no_qr')->info("Mengirim request sign dokumen dengan QR", [
                'data'    => $payload,
            ]);

            // Panggil API eksternal
            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->attach(
                    'file',                 // nama field file sesuai yang diminta API
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )->post(env('BASE_URL_BSRE') . '/sign/pdf', $payload);

            if ($response->successful()) {
                Log::channel('signature_no_qr')->info("Dokumen berhasil ditandatangani dengan QR", [
                    'nik' => $payload['nik']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil ditandatangani dengan QR',
                    'data'    => $response->headers()
                ]);
            }

            Log::channel('signature_no_qr')->error("Gagal menandatangani dokumen dengan QR", [
                'status'  => $response->status(),
                'error'   => $response->json()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandatangani dokumen dengan QR',
                'error'   => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            Log::channel('signature_no_qr')->error("Exception saat validasi sign dokumen tanpa QR", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal pada server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function downloadFile($id)
    {
        $tempFilePath = storage_path('app/private/temp_' . $id . '.pdf');

        try {
            Log::info('DownloadFile Request Initiated', [
                'url' => env('BASE_URL_BSRE') . '/sign/download/' . $id,
            ]);

            $http = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'));
            $response = $http->get(env('BASE_URL_BSRE') . '/sign/download/' . $id);

            // Log response status
            Log::info('DownloadFile Response Status', [
                'status' => $response->status(),
            ]);

            // Handle success
            if ($response->successful()) {
                file_put_contents($tempFilePath, $response->body());
                Log::info('File temporarily saved', ['path' => $tempFilePath]);

                return response()->download($tempFilePath, 'document.pdf')->deleteFileAfterSend(true);
            }

            // Handle failed response
            Log::error('DownloadFile API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat mendownload file',
                'detail' => $response->body(),
            ], $response->status());
        } catch (RequestException $e) {
            Log::error('DownloadFile Exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat melakukan request ke API',
                'detail' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('DownloadFile General Exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan internal',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifySign(Request $request)
    {
        try {
            Log::info('Mulai proses verifikasi tanda tangan digital');

            // Validasi file PDF max 5MB
            $request->validate([
                'signed_file' => 'required|mimes:pdf|max:5120',
            ]);
            Log::info('Validasi berhasil', ['filename' => $request->file('signed_file')->getClientOriginalName()]);

            $file = $request->file('signed_file');

            // Kirim ke API eksternal
            Log::info('Mengirim file ke API verifikasi', ['api_url' => env('BASE_URL_BSRE') . '/sign/verify']);

            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->attach(
                    'signed_file',
                    file_get_contents($file),
                    $file->getClientOriginalName()
                )->post(env('BASE_URL_BSRE') . '/sign/verify');

            Log::info('Response diterima dari API', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            // Cek response
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Verifikasi berhasil dengan JSON', [$data]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'File berhasil diverifikasi',
                    'data' => $data,
                ]);
            }

            Log::warning('Verifikasi gagal', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Verifikasi gagal',
                'error' => $response->body()
            ], $response->status());
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal', ['errors' => $e->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi input gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat verifikasi', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan internal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
