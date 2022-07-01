<?php

namespace App\Http\Controllers;

use App\Models\Locale;
use Cookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class FrontendController extends Controller
{
    protected function getPartnerTracking()
    {
        $pid = Request::input('pid');

        if (! is_null($pid)) {
            Cookie::queue('partnerTracking', $pid);
            $pidTracking = ['pid' => $pid];
        } else {
            $pidTracking = (Cookie::get('partnerTracking')) ? ['pid' => Cookie::get('partnerTracking')] : [];
        }

        return $pidTracking;
    }

    protected function getLocale(): Locale
    {
        $lang = Request::input('lang');

        return ($lang && Locale::has($lang)) ? Locale::get($lang) : Locale::current();
    }

    /**
     * @param $routeName
     *
     * @return RedirectResponse
     */
    private function redirect($routeName)
    {
        $parameters = array_merge(['lang' => $this->getLocale()->getValue()], $this->getPartnerTracking());

        return Redirect::away(route($routeName, $parameters), 301)
            ->header('Cache-Control', 'no-cache, max-age=0');
    }

    private function handleRoute($routeName)
    {
        return $this->redirect($routeName);
    }

    public function index()
    {
        return $this->handleRoute('landingpage');
    }

    public function imprint()
    {
        return $this->handleRoute('imprint');
    }

    public function privacyPolicy()
    {
        return $this->handleRoute('privacy-policy');
    }

    public function tutorial()
    {
        return $this->handleRoute('tutorial');
    }

    public function faq()
    {
        return $this->handleRoute('faq');
    }
}
