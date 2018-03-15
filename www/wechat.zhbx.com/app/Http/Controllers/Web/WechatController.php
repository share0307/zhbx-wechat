<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

class WechatController extends WebBaseController{
    

    /**
     * 验证微信服务器
     * @author  jianwei
     */
    public function AuthEchostr()
    {
        $wechat = app('Wechat');
    
        $response = $wechat->server->serve();
        
        return $response->send();
    }


    /**
     * 微信登录回调地址
     * @author  jianwei
     */
    public function OauthRedirect(Request $request)
    {
        var_dump($request->input());
    }
    
}
