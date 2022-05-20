<?php

use App\Mail\LeadRegisterMail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mail Routes
|--------------------------------------------------------------------------
|
| expose layouts to testing mail layouts
|
*/

Route::get('register', function () {
    $user = \App\Models\User::first();

    return (new \App\Notifications\Register($user))->toMail($user);
});


Route::get('lead-register', function () {
    $user = \App\Models\User::factory()->make();

    return new LeadRegisterMail($user);
});


Route::get('pw-reset', function () {
    $user = \App\Models\User::first();

    return (new \App\Notifications\ResetPassword($user, 'token'))->toMail($user);
});
