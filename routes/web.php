<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\IllustrationController;
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
Route::get('/impressum', [FrontendController::class, 'imprint']);
Route::get('/datenschutz', [FrontendController::class, 'dataPrivacy']);
Route::get('/tutorial', [FrontendController::class, 'tutorial']);
Route::get('/faq', [FrontendController::class, 'faq']);

Route::get('/business-illustration.svg', [IllustrationController::class, 'get']);

Route::domain(config('app.app-www-url'))
    ->group(function () {
        Route::get('/')->name('landingpage');
        Route::get('/impressum')->name('imprint');
        Route::get('/datenschutzerklaerung')->name('data-privacy');
        Route::get('/tutorial')->name('tutorial');
        Route::get('/faq')->name('faq');
    });
