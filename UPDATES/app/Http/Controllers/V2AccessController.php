<?php

namespace App\Http\Controllers;

class V2AccessController extends Controller
{
    public function __invoke()
    {
        return view('v2.access');
    }
}
