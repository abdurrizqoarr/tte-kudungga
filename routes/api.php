<?php

use App\Http\Controllers\SignTteController;
use Illuminate\Support\Facades\Route;

Route::post('/sign/pdf', [SignTteController::class, 'signFile']);
Route::post('/sign/resume-ralan', [SignTteController::class, 'resumeRalan']);

Route::get('/sign/download/{id_dokumen}', [SignTteController::class, 'downloadFile']);
Route::post('/sign/verify', [SignTteController::class, 'verifySign']);
