<?php

use App\Http\Controllers\InformController;
use Illuminate\Support\Facades\Route;

Route::get('/verifikasi-dokumen', [InformController::class, 'informPage']);
