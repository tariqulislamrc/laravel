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

Route::group([
    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers\Auth',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

});

Route::get('/config', [App\Http\Controllers\Config\ConfigController::class, 'config']);

Route::group(['middleware' => 'jwt.auth'], function(){
		// Upload Routes
	Route::post('/upload', [App\Http\Controllers\Upload\UploadController::class, 'upload']);
	Route::post('/upload/extension', [App\Http\Controllers\Upload\UploadController::class, 'getAllowedExtension']);
	Route::post('/upload/image', [App\Http\Controllers\Upload\UploadController::class, 'uploadImage']);
	Route::post('/upload/fetch', [App\Http\Controllers\Upload\UploadController::class, 'fetch']);
    Route::post('/upload/{id}', [App\Http\Controllers\Upload\UploadController::class, 'destroy']);
    

	Route::get('/product', [App\Http\Controllers\Product\ProductController::class, 'index']);
	Route::get('/product/{uuid}', [App\Http\Controllers\Product\ProductController::class, 'show']);
	Route::post('/product', [App\Http\Controllers\Product\ProductController::class, 'store']);
	Route::patch('/product/{uuid}', [App\Http\Controllers\Product\ProductController::class, 'update']);
	Route::delete('/product/{uuid}', [App\Http\Controllers\Product\ProductController::class, 'destroy']);
});
