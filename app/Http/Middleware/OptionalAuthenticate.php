<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class OptionalAuthenticate extends Middleware
{
    protected function authenticate($request, array $guards)
    {
        try {
            parent::authenticate($request, $guards);
        } catch (AuthenticationException $exception) {
        }
    }
}
