<?php

namespace App\Http\Controllers;

use App\Models\Locale;
use Cookie;
use http\QueryString;
use http\Url;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

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

    protected function getLocale(): Locale
    {
        $lang = Request::input('lang');

        return ($lang && Locale::has($lang)) ? Locale::get($lang) : Locale::current();
    }

    protected array $pathMap = [
        Locale::DE => [
            'landingpage'  => '/',
            'imprint'      => '/impressum',
            'data-privacy' => '/datenschutzerklaerung',
            'tutorial'     => '/tutorial',
            'faq'          => '/faq',
        ],
        Locale::EN => [
            'landingpage'  => '/en',
            'imprint'      => '/en/impressum',
            'data-privacy' => '/en/data-protection',
            'tutorial'     => '/en/tutorial',
            'faq'          => '/en/faq',
        ],
    ];

    protected function getPath($locale, $routeName): ?string
    {
        return Arr::get($this->pathMap, "{$locale->getValue()}.$routeName");
    }

    /**
     * @param $routeName
     *
     * @return RedirectResponse
     */
    private function redirect($routeName)
    {
        $path = $this->getPath($this->getLocale(), $routeName)
            ?? $this->getPath(Locale::DE(), $routeName)
            ?? '/';

        $parameters = $this->getPartnerTracking();

        $base = config('app.app-www-url');
        $url = new Url($base, [
            'path'  => $path,
            'query' => new QueryString($parameters),
        ]);

        return Redirect::away($url, 301);
    }

    private function handleRoute($routeName)
    {
        if (App::environment('local')) {
            return view($routeName)->with('pidTracking', $this->getPartnerTracking());
        }

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

    public function dataPrivacy()
    {
        return $this->handleRoute('data-privacy');
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
