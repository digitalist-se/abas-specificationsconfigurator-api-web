<?php

use App\Mail\LeadRegisterMail;
use App\Models\User;
use App\Notifications\Register;
use App\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mail Routes
|--------------------------------------------------------------------------
|
| expose layouts to testing mail layouts
|
*/

Route::get('{mail}', static function (Request $request, $mail) {
    $user = User::first();

    if ($lang = $request->input('lang')) {
        $locale = \App\Models\Locale::get($lang);
        App::setLocale($locale->getValue());
    }

    return match ($mail) {
        'register'      => (new Register($user))->toMail($user),
        'lead-register' => new LeadRegisterMail(User::factory()->make()),
        'pw-reset'      => (new ResetPassword($user, 'token'))->toMail($user),
        default         => abort(404),
    };
});
