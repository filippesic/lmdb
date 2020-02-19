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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::get('/test', 'VideoController@asdf');

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::middleware('auth:api')->post('logout', 'AuthController@logout');


Route::get('artists/directors', 'ArtistController@directors');
Route::get('videos/top', 'VideoController@top');
Route::get('artists/actors', 'ArtistController@actors');
Route::get('videos/search', 'VideoController@search');
Route::get('/user', 'UserController@user')->middleware('auth:api');
Route::post('/user/rate', 'UserController@rate')->middleware('auth:api');
Route::post('/user/unrate', 'UserController@unrate')->middleware('auth:api');


Route::apiResource('users', 'UserController')->middleware('auth:api'); //->except(['store']);
Route::apiResource('artists', 'ArtistController');
Route::apiResource('genres', 'GenreController');
Route::apiResource('videos', 'VideoController');


//Route::get('videos/average/{video}', 'VideoController@average');


//Route::middleware('auth:api')->group(function () {
//
//    Route::get('users', 'UserController@index');
//    Route::get('users/{user}', 'UserController@show');
//    Route::delete('users/{user}', 'UserController@destory');
//    Route::put('users/{user}', 'UserController@update');
//
//});

