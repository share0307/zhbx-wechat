<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WechatController extends Controller{

    /**
     * 验证服务器
     * @author  jianwei
     */
    function test(Request $request,Response $response){
        $echostr = "hello wordl!";
        if($request->has('echostr')) {
            $echostr = $request->get("echostr");
        }
        
        return $echostr;
    }





}
