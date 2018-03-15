<?php

namespace App\Http\Business;

use App\Exceptions\JsonException;

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
    public function getWeixinUserInfo()
    {
        $Wechat = app('Wechat');
        $oauth = $Wechat->oauth;
    
        $wx_user = $oauth->user();
        
        var_dump($wx_user);
        exit();
    }
    
    
}
