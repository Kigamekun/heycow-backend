<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function indexlp()
    {

        return view('index_lp');
    }
}
