<?php

namespace App\Http\Controllers;

use App\Models\RiwayatResumeRalan;
use App\Models\RiwayatResumeRanap;
use Illuminate\Http\Request;

class InformController extends Controller
{
    public function informPageRalan($idDokumen)
    {
        $dokumen = RiwayatResumeRalan::where('id', $idDokumen)->first();

        return view('inform', ["data" => $dokumen]);
    }

    public function informPageRanap($idDokumen)
    {
        $dokumen = RiwayatResumeRanap::where('id', $idDokumen)->first();

        return view('inform', ["data" => $dokumen]);
    }
}
