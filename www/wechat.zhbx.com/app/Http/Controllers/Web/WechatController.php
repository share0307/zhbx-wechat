<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\JsonException;
use App\Http\Business\UsersBusiness;
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
    public function OauthRedirect(Request $request, WechatOauthBusiness $wechat_oauth_business, UsersBusiness $users_business)
    {
        $target_url = $request->get('target_url',url('/'));
    
        $code = $request->get('code');
        
        //从微信服务器获取用户信息
        $wechat_user_data = $wechat_oauth_business->getWeixinUserInfo($code);

        //获取 openid
        $openid = $wechat_user_data['id'];
        
        try {
            //检查用户是否存在
            $users_business->GetUserInfoByOpenId($openid);
            
            //跳转到最后
            goto redirect;
        }catch (JsonException $e){
            if($e->getCode() != 30001){
                //直接返回首页吧
                return redirect('/');
            }else if($e->getCode() == 30001 && !isset($wechat_user_data['nickname'])){
                //跳转跳转到用户授权的页面，再次授权
                return $wechat_oauth_business->WechatOauthLogin($request,'snsapi_userinfo',$target_url);
            }
        }
        
//        dd($wechat_user_data);
        
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
        
//        同步用户数据
//        dd($wx_user_info);
        
        //把数据插入到数据库
        $users_business->CreateUser($wx_user_info);
        
        
        redirect:
        //把用户信息写入 session，然后跳转
        
        
        return redirect($target_url);
    }
    
}
