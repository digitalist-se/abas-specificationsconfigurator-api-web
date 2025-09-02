<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AcceptLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $localeSet = Locale::activatedSet();
        if (($user = $request->user()) && $user->role->is(Role::ADMIN)) {
            $localeSet = Locale::supportedSet();
        }

        $activeLocales = $localeSet->getValues();

        if ($locale = $request->getPreferredLanguage($activeLocales)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
