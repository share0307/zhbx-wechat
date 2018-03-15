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

Route::middleware([])->group(function(){
    
    //验证微信服务器
    Route::get('/',"WechatController@AuthEchostr");
    
    
});
