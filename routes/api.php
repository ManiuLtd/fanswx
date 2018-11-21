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

Route::namespace('Api')->group(function () {
    Route::any('/serve', 'WeChatController@serve');
    //更新菜单
    Route::get('/menu/update', 'WeChatController@menu_update');
    //生成带参数临时二维码    参数格式id/有效天数 T_xxx/1-30
    Route::get('/qrcode/temporary/{scene_id}/{day}', 'WeChatController@qrcode_temporary')
        ->where([
            'scene_id' => '^(T_)[a-z0-9]+',
            'day' => '^([12][0-9]|30|[1-9])$',
        ]);
    //生成带参数永久二维码 参数格式id /F_xxx
    Route::get('/qrcode/forever/{scene_id}', 'WeChatController@qrcode_forever')
        ->where('scene_id', '^(F_)[a-z0-9]+');
});