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

//Route::get('/test', 'VideoController@asdf');

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::middleware('auth:api')->post('logout', 'AuthController@logout');


Route::get('videos/top', 'VideoController@top');
Route::get('videos/search', 'VideoController@search');
Route::get('artists/actors', 'ArtistController@actors');
Route::get('artists/directors', 'ArtistController@directors');
Route::get('/user', 'UserController@show')->middleware('auth:api');
Route::get('/user/list', 'UserController@list')->middleware('auth:api');
Route::get('/user/list2', 'UserController@list2')->middleware('auth:api');
Route::get('/user/rates', 'UserController@rates')->middleware('auth:api');

Route::post('/user/rate', 'UserController@rate')->middleware('auth:api');
Route::post('/user/rates2', 'UserController@rates2')->middleware('auth:api');
Route::post('/user/unrate', 'UserController@unrate')->middleware('auth:api');
Route::post('/user/addToList', 'UserController@addToList')->middleware('auth:api');


Route::apiResource('users', 'UserController')->middleware('auth:api'); //->except(['store']);
Route::apiResource('artists', 'ArtistController');
Route::apiResource('genres', 'GenreController');
Route::apiResource('videos', 'VideoController');
