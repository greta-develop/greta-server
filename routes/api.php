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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('users', 'JWTAuthController@users');

Route::post('auth/register', 'JWTAuthController@register');
Route::post('auth/login', 'JWTAuthController@login');

Route::post('test/hmac', 'HashController@index');

Route::group(['middleware' => 'jwt.auth'], function(){
    Route::get('auth/user', 'JWTAuthController@user');
    Route::get('auth/logout', 'JWTAuthController@logout');

    Route::post('groups', 'GroupController@store');
    Route::post('auth/users/banks', 'BankController@store');
});

Route::middleware('jwt.refresh')->get('users/token/refresh', 'JWTAuthController@refresh');
