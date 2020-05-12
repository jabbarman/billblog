<?php

use Illuminate\Http\Request;

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


Route::prefix('v1')->group(function () {
    Route::post('user', 'AuthenticateController@store')->name('user.create');
    Route::post('user/authenticate', 'AuthenticateController@authenticate')->name('user.authenticate');
    Route::apiResource('blog', 'BlogController');
    Route::post('blog/{blog}/upload', 'BlogController@upload');
    Route::delete('blog/{blog}/upload/{upload_id}', 'BlogController@remove');
    Route::post('blog/{blog}/label', 'BlogController@addLabel');
    Route::post('blog/{blog}/label/{label}', 'BlogController@editLabel');
    Route::delete('blog/{blog}/label/{label}', 'BlogController@delLabel');
    Route::get('search', 'BlogController@search')->name('blog.search');
});


