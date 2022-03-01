<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class FrontendController extends Controller
{
    protected function getPartnerTracking()
    {
        $pid = Request::input('pid');

        $pidTracking = null;
        if (! is_null($pid)) {
            Cookie::queue('partnerTracking', $pid);
            $pidTracking = ['pid' => $pid];
        } else {
            $pidTracking = (Cookie::get('partnerTracking')) ? ['pid' => Cookie::get('partnerTracking')] : null;
        }

        return $pidTracking;
    }

    /**
     * @param $routeName
     *
     * @return RedirectResponse
     */
    private function redirect($routeName)
    {
        return Redirect::away(route($routeName, $this->getPartnerTracking()), 301);
    }

    public function index()
    {
        if (App::environment('local')) {
            return view('landingpage')->with('pidTracking', $this->getPartnerTracking());
        }

        return $this->redirect('landingpage');
    }

    public function imprint()
    {
        if (App::environment('local')) {
            return view('imprint')->with('pidTracking', $this->getPartnerTracking());
        }

        return $this->redirect('imprint');
    }

    public function dataPrivacy()
    {
        if (App::environment('local')) {
            return view('data-privacy')->with('pidTracking', $this->getPartnerTracking());
        }

        return $this->redirect('data-privacy');
    }

    public function tutorial()
    {
        if (App::environment('local')) {
            return view('tutorial')->with('pidTracking', $this->getPartnerTracking());
        }

        return $this->redirect('tutorial');
    }

    public function faq()
    {
        if (App::environment('local')) {
            return view('faq')->with('pidTracking', $this->getPartnerTracking());
        }

        return $this->redirect('faq');
    }
}
