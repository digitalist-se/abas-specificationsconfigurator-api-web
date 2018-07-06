<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class TrustOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var Response
         */
        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        if (App::environment('local')) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } else {
            $response->headers->set('Access-Control-Allow-Origin', config('app.app-url'));
        }
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

        return $response;
    }
}
