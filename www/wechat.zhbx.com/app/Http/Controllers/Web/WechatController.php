<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\WebBaseController;

class HomeController extends WebBaseController{
    

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


}
