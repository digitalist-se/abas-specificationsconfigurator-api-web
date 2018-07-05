<?php


Route::get('/user', 'UserController@get');
Route::put('/user', 'UserController@update');
Route::put('/password', 'Auth\PasswordController@updatePassword');
Route::get('/logout', 'Auth\LogoutController@logout');

Route::get('/texts', 'TextController@list');
Route::post('/texts', 'TextController@create')
    ->middleware('can:create,App\Models\Text');
Route::put('/texts/{text}', 'TextController@update')
    ->middleware('can:update,text');

Route::get('/structure', 'StructureController@get');
Route::get('/chapters', 'ChapterController@list');
Route::get('/choice-types', 'ChoiceTypeController@list');
Route::get('/sections/{chapter}', 'SectionController@list');

Route::get('/elements/{section}', 'ElementController@list');

Route::get('/answers', 'AnswerController@list');
Route::post('/answers/reset', 'AnswerController@reset');
Route::get('/answers/{element}', 'AnswerController@get');
Route::put('/answers/{element}', 'AnswerController@update');

Route::get('/document/generate', 'DocumentController@generate');
