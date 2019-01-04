<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class LandingpageController extends Controller
{
    public function index() 
    {
        $pid = Input::get('pid');

        if (!is_null($pid)) {
            Cookie::queue('partnerTracking', $pid);
        }

        return view('landingpage');
    }
}
