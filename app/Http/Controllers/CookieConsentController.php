<?php

namespace App\Http\Controllers;

class CookieConsentController extends Controller
{
    /**
     * provide config for cookie consent popup.
     *
     * see also config docs: https://cookieconsent.insites.com/documentation/
     */
    public function get()
    {
        return [
            'palette' => [
                'popup' => [
                    'background' => '#2a3539',
                ],
                'button' => [
                    'background' => '#008bd0',
                ],
            ],
            'position' => 'bottom-right',
            'cookie'   => [
                'domain' => config('app.domain'),
            ],
            'content' => [
                'message'     => trans('cookieconsent.message'),
                    'dismiss' => trans('cookieconsent.dismiss'),
                    'link'    => trans('cookieconsent.link'),
                    'href'    => route('data-privacy'),
                ],
        ];
    }
}
