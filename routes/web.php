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

Route::get('/', function () {
    return view('landingpage');
})->name('landingpage');
Route::get('/impressum', function () {
    return view('imprint');
})->name('imprint');
Route::get('/datenschutz', function () {
    return view('data-privacy');
})->name('data-privacy');
Route::get('/tutorial', function () {
    return view('tutorial');
})->name('tutorial');
Route::get('/app/register')->name('register');
Route::get('/app/login')->name('login');

Route::get('/business-illustration.svg', 'IllustrationController@get');
