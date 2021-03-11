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

Route::get('/', [FrontendController::class, 'index'])->name('landingpage');
Route::get('/impressum', [FrontendController::class, 'imprint'])->name('imprint');
Route::get('/datenschutz', [FrontendController::class, 'dataPrivacy'])->name('data-privacy');
Route::get('/tutorial', [FrontendController::class, 'tutorial'])->name('tutorial');

Route::get('/business-illustration.svg', [IllustrationController::class, 'get']);
