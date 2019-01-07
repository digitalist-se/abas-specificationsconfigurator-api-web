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

        $get = '';
        if (!is_null($pid)) {
            Cookie::queue('partnerTracking', $pid);
            $get = '?pid='.$pid;
        } else {
            $get = (Cookie::get('partnerTracking')) ? '?pid='.Cookie::get('partnerTracking')  : '';
        }

        return view('landingpage')->with('get', $get);
    }
}
