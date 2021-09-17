<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AcceptLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $activeLocales = Locale::activatedSet()->getValues();
        if ($locale = $request->getPreferredLanguage($activeLocales)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
