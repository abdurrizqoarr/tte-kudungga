<?php

namespace App\Http\Controllers;

use App\Models\RiwayatResumeRalan;
use App\Models\RiwayatResumeRamap;
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
        $dokumen = RiwayatResumeRamap::where('id', $idDokumen)->first();

        return view('inform', ["data" => $dokumen]);
    }
}
