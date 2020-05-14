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

//文件上传接口
Route::post('upload', 'ApiController@upload')->name('api.upload');
//呼叫接口
Route::post('dial','ApiController@dial')->name('api.dial');
//挂断接口
Route::post('hangup','ApiController@hangup')->name('api.hangup');