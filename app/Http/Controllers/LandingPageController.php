<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MyEvent;
class LandingPageController extends Controller
{
    public function indexlp()
    {

        return view('index_lp');
    }

    public function test(){
        $status = $_GET['status'];
        event(new MyEvent($status));

    }
}
