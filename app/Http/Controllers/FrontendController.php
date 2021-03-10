<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Support\Facades\Request;

class FrontendController extends Controller
{
    protected function getPartnerTracking()
    {
        $pid = Request::input('pid');

        $pidTracking = null;
        if (!is_null($pid)) {
            Cookie::queue('partnerTracking', $pid);
            $pidTracking = ['pid' => $pid];
        } else {
            $pidTracking = (Cookie::get('partnerTracking')) ? ['pid' => Cookie::get('partnerTracking')] : null;
        }

        return $pidTracking;
    }

    public function index()
    {
        return view('landingpage')->with('pidTracking', $this->getPartnerTracking());
    }

    public function imprint()
    {
        return view('imprint')->with('pidTracking', $this->getPartnerTracking());
    }

    public function dataPrivacy()
    {
        return view('data-privacy')->with('pidTracking', $this->getPartnerTracking());
    }

    public function tutorial()
    {
        return view('tutorial')->with('pidTracking', $this->getPartnerTracking());
    }
}
