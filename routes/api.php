<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

/*
 * create new user.
 */
Route::post('/user', [UserController::class, 'create']);

/*
 * sends email to with reset token to
 */
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

/*
 * reset password
 */
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);

/*
 * get cookie consent config
 */
Route::get('/cookieconsent', [CookieConsentController::class, 'get'])->name('cookieconsent');
