<?php

namespace App\Http\Controllers\Web;

use App\Http\Business\WechatOauthBusiness;
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
    public function OauthRedirect(Request $request, WechatOauthBusiness $wechat_oauth_business)
    {
        $target_url = $request->get('target_url',url('/'));
    
        $code = $request->get('code');
        
        $wechat_user_data = $wechat_oauth_business->getWeixinUserInfo($code);
        
        //同步用户数据
        dd($wechat_user_data);
        
        return redirect($target_url);
    }
    
}
