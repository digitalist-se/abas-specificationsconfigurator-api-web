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

Route::get('/', 'FrontendController@landingpage')->name('landingpage');
Route::get('/impressum', 'FrontendController@imprint')->name('imprint');
Route::get('/datenschutz', 'FrontendController@dataPrivacy')->name('data-privacy');
Route::get('/tutorial', 'FrontendController@tutorial')->name('tutorial');

Route::get('/business-illustration.svg', 'IllustrationController@get');
