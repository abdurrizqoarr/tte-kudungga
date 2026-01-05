<?php

use App\Http\Controllers\InformController;
use Illuminate\Support\Facades\Route;

Route::get('/verifikasi-dokumen-ralan/{idDokumen}', [InformController::class, 'informPageRalan']);
Route::get('/verifikasi-dokumen-ranap/{idDokumen}', [InformController::class, 'informPageRanap']);
