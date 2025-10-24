<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\IllustrationController;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [FrontendController::class, 'index']);
Route::get('/imprint', [FrontendController::class, 'imprint']);
Route::get('/privacy-policy', [FrontendController::class, 'privacyPolicy']);
Route::get('/tutorial', [FrontendController::class, 'tutorial']);
Route::get('/faq', [FrontendController::class, 'faq']);

Route::get('/business-illustration.svg', [IllustrationController::class, 'get']);

Route::domain(config('app.app-www-url'))
    ->group(function () {
        Route::prefix('/{lang}')
            ->where(['lang' => '[A-Za-z]{2}'])
            ->group(function ($lang = App\Models\Locale::DE) {
                Route::get('/')->name('landingpage');
                Route::get('/imprint')->name('imprint');
                Route::get('/privacy-policy')->name('privacy-policy');
                Route::get('/tutorial')->name('tutorial');
                Route::get('/faq')->name('faq');
            });
    });

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect(route('login'));
})
    ->middleware(['signed'])
    ->name('verification.verify');
