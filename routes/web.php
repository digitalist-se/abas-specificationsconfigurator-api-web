<?php

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

Route::get('/', 'FrontendController@index');
Route::get('/impressum', 'FrontendController@imprint');
Route::get('/datenschutz', 'FrontendController@dataPrivacy');
Route::get('/tutorial', 'FrontendController@tutorial');

Route::get('/business-illustration.svg', 'IllustrationController@get');

Route::domain(config('app.app-www-url'))
    ->group(function () {
        Route::get('/')->name('landingpage');
        Route::get('/impressum')->name('imprint');
        Route::get('/datenschutz')->name('data-privacy');
        Route::get('/tutorial')->name('tutorial');
    });
