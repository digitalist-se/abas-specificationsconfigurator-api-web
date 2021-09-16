<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChoiceTypeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ElementController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\TextController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'get']);
Route::put('/user', [UserController::class, 'update']);
Route::put('/password', [PasswordController::class, 'updatePassword']);
Route::get('/logout', [LogoutController::class, 'logout']);

Route::get('/locales/supported', [LocaleController::class, 'supported']);
Route::get('/locales/activated', [LocaleController::class, 'activated']);

Route::get('/texts', [TextController::class, 'list']);
Route::post('/texts', [TextController::class, 'create'])
    ->middleware('can:create,App\Models\Text');
Route::put('/texts/{text}', [TextController::class, 'update'])
    ->middleware('can:update,text');

Route::get('/structure', [StructureController::class, 'get']);
Route::get('/chapters', [ChapterController::class, 'list']);
Route::get('/choice-types', [ChoiceTypeController::class, 'list']);
Route::get('/sections/{chapter}', [SectionController::class, 'list']);

Route::get('/elements/{section}', [ElementController::class, 'list']);

Route::get('/answers', [AnswerController::class, 'list']);
Route::post('/answers/reset', [AnswerController::class, 'reset']);
Route::get('/answers/{element}', [AnswerController::class, 'get']);
Route::put('/answers/{element}', [AnswerController::class, 'update']);

Route::get('/document/generate', [DocumentController::class, 'generate']);
