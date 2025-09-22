<?php

namespace App\Http\Controllers;

use App\Models\SignDokumen;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SignTteController extends Controller
{
    public function signFile(Request $request)
    {
        $body = $request->except(['file', 'imageTTD']); // ambil semua kecuali file
        $filePaths = [];

        try {
            Log::info('SignFile Request Initiated', [
                'url'  => env('BASE_URL_BSRE') . '/sign/pdf',
                'body' => $body,
            ]);

            // Simpan file yang diupload ke storage/app/private
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $randomName = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('private', $randomName, 'local'); // storage/app/private
                $filePaths['file'] = storage_path('app/private/' . $filePath);
            }

            if ($request->hasFile('imageTTD')) {
                $imageTTD = $request->file('imageTTD');
                $randomName = Str::random(20) . '.' . $imageTTD->getClientOriginalExtension();
                $imagePath = $imageTTD->storeAs('private', $randomName, 'local'); // storage/app/private
                $filePaths['imageTTD'] = storage_path('app/private/' . $imagePath);
            }

            // Buat request HTTP dengan attach file
            $http = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'));

            if (isset($filePaths['file'])) {
                $http->attach('file', file_get_contents($filePaths['file']), $request->file('file')->getClientOriginalName());
            }

            if (isset($filePaths['imageTTD'])) {
                $http->attach('imageTTD', file_get_contents($filePaths['imageTTD']), $request->file('imageTTD')->getClientOriginalName());
            }

            $response = $http->post(env('BASE_URL_BSRE') . '/sign/pdf', $body);
            $response->throw();

            Log::info('SignFile Response Received', [
                'status'   => $response->status(),
                'body'     => $response->json(),
                'headers'  => $response->headers(),
            ]);

            $signDokumen = SignDokumen::create([
                'nik' => $request->input('nik'),
                'dokumen_asli' => $filePaths['file'] ? basename($filePaths['file']) : null,
                'id_dokumen_ttd' => $response->headers()['id_dokumen'][0] ?? null,
                'image_ttd' => "uji coba"
            ]);

            return response()->json([
                'message' => 'Berhasil',
                'data' => $signDokumen
            ], 200);
        } catch (RequestException $e) {
            Log::error('SignFile API Error', [
                'status' => $e->response?->status(),
                'body'   => $e->response?->body(),
                'headers' => $e->response?->headers(),
                'trace'  => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat melakukan request',
                'detail' => $e->response?->body(),
            ], $e->response?->status() ?? 500);
        } finally {
            foreach ($filePaths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function resumeRalan(Request $request)
    {
        // ambil input
        $resume     = $request->input('resume');
        $nik        = $request->input('nik');
        $passphrase = $request->input('passphrase');

        // helper: mask sensitive values (mis. NIK, passphrase jangan pernah log penuh)
        $mask = function (?string $val, $start = 2, $end = 2) {
            if (!$val) return null;
            $len = mb_strlen($val);
            if ($len <= ($start + $end)) return str_repeat('*', $len);
            return mb_substr($val, 0, $start) . str_repeat('*', $len - ($start + $end)) . mb_substr($val, -$end);
        };

        Log::info('resumeRalan invoked', [
            'no_rawat' => data_get($resume, 'no_rawat'),
            'nik_mask' => $mask($nik),
            'request_ip' => $request->ip(),
        ]);

        // validasi sederhana
        if (!$resume) {
            Log::warning('Resume kosong / tidak ditemukan', ['input_present' => $request->has('resume')]);
            return response()->json(['error' => 'Data resume tidak ditemukan'], 400);
        }
        if (!$nik || !$passphrase) {
            Log::warning('Credential untuk signing tidak lengkap', [
                'nik_present' => (bool) $nik,
                'passphrase_present' => (bool) $passphrase,
            ]);
            return response()->json(['error' => 'Nik atau passphrase tidak boleh kosong'], 400);
        }

        // generate PDF
        try {
            Log::info('Mulai generate PDF', ['view' => 'resume-ralan', 'no_rawat' => data_get($resume, 'no_rawat')]);

            $pdf = Pdf::loadView('resume-ralan', compact('resume'))->setPaper('A4');
            $pdfContent = $pdf->output();

            Log::info('PDF berhasil digenerate', [
                'bytes' => strlen($pdfContent),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal generate PDF', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Gagal generate PDF',
                'message' => $e->getMessage(),
            ], 500);
        }

        // siapkan filename
        $filename = 'resume_ralan_' . Carbon::now()->timestamp . '.pdf';

        // kirim ke API signing tanpa menyimpan di server
        $apiUrl = rtrim(env('BASE_URL_BSRE', ''), '/') . '/sign/pdf';
        Log::info('Mulai request ke signing API', [
            'endpoint' => $apiUrl,
            'username_env_set' => !empty(env('USERNAME_BSRE')),
            'no_rawat' => data_get($resume, 'no_rawat'),
        ]);

        try {
            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->timeout(60)          // timeout request
                ->attach('file', $pdfContent, $filename)
                ->post($apiUrl, [
                    'nik' => $nik,
                    'passphrase' => $passphrase,
                    'tampilan' => 'invisible',
                ]);

            // log status dan beberapa header penting (tanpa mengeluarkan header sensitif)
            Log::info('Response dari signing API diterima', [
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
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
            Log::error('Signing API mengembalikan error', [
                'status' => $response->status(),
                'body_preview' => is_string($respBody) ? substr($respBody, 0, 2000) : $respBody,
            ]);

            return response()->json([
                'error' => 'Signing API error',
                'status' => $response->status(),
                'body' => $respBody,
            ], $response->status() ?: 500);
        } catch (\Throwable $e) {
            // error koneksi / timeout / exception lain
            Log::error('Koneksi ke signing API gagal', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

        $mask = function (?string $val, $start = 2, $end = 2) {
            if (!$val) return null;
            $len = mb_strlen($val);
            if ($len <= ($start + $end)) return str_repeat('*', $len);
            return mb_substr($val, 0, $start) . str_repeat('*', $len - ($start + $end)) . mb_substr($val, -$end);
        };

        Log::info('resumeRanap invoked', [
            'no_rawat' => data_get($resume, 'no_rawat'),
            'nik_mask' => $mask($nik),
            'request_ip' => $request->ip(),
        ]);

        // validasi sederhana
        if (!$resume) {
            Log::warning('Resume kosong / tidak ditemukan', ['input_present' => $request->has('resume')]);
            return response()->json(['error' => 'Data resume tidak ditemukan'], 400);
        }
        if (!$nik || !$passphrase) {
            Log::warning('Credential untuk signing tidak lengkap', [
                'nik_present' => (bool) $nik,
                'passphrase_present' => (bool) $passphrase,
            ]);
            return response()->json(['error' => 'Nik atau passphrase tidak boleh kosong'], 400);
        }

        // generate PDF
        try {
            Log::info('Mulai generate PDF', ['view' => 'resume-ranap', 'no_rawat' => data_get($resume, 'no_rawat')]);

            $pdf = Pdf::loadView('resume-ranap', compact('resume'))->setPaper('A4');
            $pdfContent = $pdf->output();

            Log::info('PDF berhasil digenerate', [
                'bytes' => strlen($pdfContent),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal generate PDF', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Gagal generate PDF',
                'message' => $e->getMessage(),
            ], 500);
        }

        // siapkan filename
        $filename = 'resume_ranap_' . Carbon::now()->timestamp . '.pdf';

        // kirim ke API signing tanpa menyimpan di server
        $apiUrl = rtrim(env('BASE_URL_BSRE', ''), '/') . '/sign/pdf';
        Log::info('Mulai request ke signing API', [
            'endpoint' => $apiUrl,
            'username_env_set' => !empty(env('USERNAME_BSRE')),
            'no_rawat' => data_get($resume, 'no_rawat'),
        ]);

        try {
            $response = Http::withBasicAuth(env('USERNAME_BSRE'), env('PASSWORD_BSRE'))
                ->timeout(60)
                ->attach('file', $pdfContent, $filename)
                ->post($apiUrl, [
                    'nik' => $nik,
                    'passphrase' => $passphrase,
                    'tampilan' => 'invisible',
                ]);

            // log status dan beberapa header penting (tanpa mengeluarkan header sensitif)
            Log::info('Response dari signing API diterima', [
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
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
            Log::error('Signing API mengembalikan error', [
                'status' => $response->status(),
                'body_preview' => is_string($respBody) ? substr($respBody, 0, 2000) : $respBody,
            ]);

            return response()->json([
                'error' => 'Signing API error',
                'status' => $response->status(),
                'body' => $respBody,
            ], $response->status() ?: 500);
        } catch (\Throwable $e) {
            // error koneksi / timeout / exception lain
            Log::error('Koneksi ke signing API gagal', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Koneksi ke signing API gagal',
                'message' => $e->getMessage(),
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

            $response = Http::attach(
                'signed_file',
                file_get_contents($file),
                $file->getClientOriginalName()
            )->post(env('BASE_URL_BSRE') . '/sign/verify');

            Log::info('Response diterima dari API', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => substr($response->body(), 0, 500) // potong biar gak kebanyakan di log
            ]);

            $contentType = $response->header('Content-Type');

            // Cek response
            if ($response->successful()) {
                if (str_contains($contentType, 'application/json')) {
                    $data = $response->json();
                    Log::info('Verifikasi berhasil dengan JSON', $data);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'File berhasil diverifikasi',
                        'data' => $data,
                    ]);
                } else {
                    $html = $response->body();
                    Log::warning('API mengembalikan HTML, bukan JSON', ['html_snippet' => substr($html, 0, 200)]);

                    return response()->json([
                        'status' => 'error',
                        'message' => 'API tidak mengembalikan JSON, dapat HTML',
                        'html' => $html,
                    ], 500);
                }
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
