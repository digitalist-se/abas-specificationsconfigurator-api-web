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

        $pidTracking = null;
        if (!is_null($pid)) {
            Cookie::queue('partnerTracking', $pid);
            $pidTracking = ['pid' => $pid ];
        } else {
            $pidTracking = (Cookie::get('partnerTracking')) ? ['pid' => Cookie::get('partnerTracking')]  : null;
        }

        return view('landingpage')->with('pidTracking', $pidTracking);
    }
}
