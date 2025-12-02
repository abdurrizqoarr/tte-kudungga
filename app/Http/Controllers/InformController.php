<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InformController extends Controller
{
    public function informPage()
    {
        return view('inform');
    }
}
