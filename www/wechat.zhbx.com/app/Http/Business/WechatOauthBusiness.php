<?php

namespace App\Http\Business;

use App\Exceptions\JsonException;
use Overtrue\Socialite\AuthorizeFailedException;

class WechatOauthBusiness extends BusinessBase{
    
    
    /**
     * 构造方法
     * @author  jianwei
     */
    public function __construct()
    {
    
    }
    
    /**
     * 检查微信是否登录
     * @author  jianwei
     */
    public function checkWechatLogin()
    {
        //从 session 中判断用户是否登录
        $user_id = session('user_id');
        
        $openid = session('wechat.openid');
        
        if(!is_numeric($user_id) || $user_id < 1 || empty($openid)){
            throw new JsonException(30000);
        }
        
        
        return true;
    }
    
    
    /**
     * 授权登录后，获取微信用户数据
     * @author  jianwei
     */
    public function getWeixinUserInfo($code)
    {
        if(empty($code)){
            throw new JsonException(10000);
        }
    
//        $wx_user_arr = array();
        
        $Wechat = app('Wechat');
        $oauth = $Wechat->oauth;
    
        try {
            $wx_user = $oauth->user();
        }catch (AuthorizeFailedException $e){
            throw new JsonException(20001);
        }
    
        $wx_user_arr = $wx_user->toArray();
    
        $wx_user_info = array();
        $wx_user_info['openid'] = $wx_user_arr['id'];
        $wx_user_info['nickname'] = $wx_user_arr['nickname'];
        $wx_user_info['avatar'] = $wx_user_arr['avatar'];
        $wx_user_info['email'] = $wx_user_arr['email'];
        $wx_user_info['sex'] = $wx_user_arr['original']['sex'];
        $wx_user_info['language'] = $wx_user_arr['original']['language'];
        $wx_user_info['city'] = $wx_user_arr['original']['city'];
        $wx_user_info['province'] = $wx_user_arr['original']['province'];
        $wx_user_info['country'] = $wx_user_arr['original']['country'];
        $wx_user_info['headimgurl'] = $wx_user_arr['original']['headimgurl'];
        
        return $wx_user_arr;
    }
    
    
}
