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
    Route::post('user', 'AuthenticateController@store');
    Route::post('user/authenticate', 'AuthenticateController@authenticate');
    Route::apiResource('blog', 'BlogController');
    Route::post('blog/{blog}/upload', 'BlogController@upload');
});


