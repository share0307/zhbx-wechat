<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function (\Illuminate\Http\Request $request) {
//    if($request->has('echostr')){
//        return $request->get('echostr');
//    }
//    return view('welcome');
//});

Route::middleware(['CheckWxBrowser','CheckWxLogin',])->group(function(){
    
    //验证微信服务器
//    Route::get('/',"WechatController@AuthEchostr");
    Route::get('/','HomeController@index');
    
});

//微信相关
Route::middleware(['CheckWxBrowser',])->prefix('wechat')->group(function() {
    
    //微信授权后的跳转页面
    Route::get('/redirect','WechatController@OauthRedirect');
});

