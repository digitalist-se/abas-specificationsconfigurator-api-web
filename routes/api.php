<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/**
 * create new user.
 */
Route::post('/user', 'UserController@create');

/*
 * sends email to with reset token to
 */
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');

/*
 * reset password
 */
Route::post('/password/reset', 'Auth\ResetPasswordController@reset');
