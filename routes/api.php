<?php

use App\Http\Controllers\SignTteController;
use Illuminate\Support\Facades\Route;

Route::post('/sign/resume-ralan', [SignTteController::class, 'resumeRalan']);
Route::post('/sign/resume-ranap', [SignTteController::class, 'resumeRanap']);
Route::post('/sign/dokumen-with-qr', [SignTteController::class, 'signDokumenWithQr']);
Route::post('/sign/dokumen-no-qr', [SignTteController::class, 'signDokumenNoQr']);

Route::get('/sign/download/{id_dokumen}', [SignTteController::class, 'downloadFile']);
Route::post('/sign/verify', [SignTteController::class, 'verifySign']);
