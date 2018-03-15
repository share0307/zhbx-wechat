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
    
        
        $wx_user_info = array();
        $wx_user_info['openid'] = $wechat_user_data['id'];
        $wx_user_info['nickname'] = $wechat_user_data['nickname'];
        $wx_user_info['avatar'] = $wechat_user_data['avatar'];
        $wx_user_info['email'] = $wechat_user_data['email'];
        if(!empty($wechat_user_data['original'])) {
            $wx_user_info['sex'] = $wechat_user_data['original']['sex'];
            $wx_user_info['language'] = $wechat_user_data['original']['language'];
            $wx_user_info['city'] = $wechat_user_data['original']['city'];
            $wx_user_info['province'] = $wechat_user_data['original']['province'];
            $wx_user_info['country'] = $wechat_user_data['original']['country'];
            $wx_user_info['headimgurl'] = $wechat_user_data['original']['headimgurl'];
        }
        
        //同步用户数据
        dd($wx_user_info);
        
        return redirect($target_url);
    }
    
}
