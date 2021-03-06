<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'middleware' => 'api',
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

    Route::group(['prefix' => 'dashboard'], function () {
        Route::resource('actors', 'ActorController');
    });

    Route::group(['namespace' => 'Admin'], function () {
        Route::resource('movies', 'MovieController');
        Route::resource('actors', 'ActorController');
        Route::resource('categories', 'CategoryController')->only('index', 'store');
        Route::put('set-categories', 'CategoryController@update');
        Route::get('movies_trash', 'MovieController@moviesTrash');
        Route::put('movies/{id}/restore', 'MovieController@restore');
    });

});

Route::get('/index', 'MoviesController@index');
Route::get('/movie/{id}', 'MoviesController@show');
Route::get('/search', 'MoviesController@search');