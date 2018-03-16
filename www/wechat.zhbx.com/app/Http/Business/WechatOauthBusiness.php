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
    public function checkWechatIsLogin()
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
        
        return $wx_user_arr;
    }
    
    
    /**
     * 检查是否已经微信登录的业务
     * @author  jianwei
     */
    public function WechatOauthLogin($request,$scopes,$fullurl = '')
    {
        //跳转到登录页面
        $oauth = app('Wechat')->oauth;
        //需要用户授权取得详细信息
//        $oauth->scopes(['snsapi_userinfo']);
//        $oauth->scopes(['snsapi_base']);
        $oauth->scopes([$scopes]);
        //回调地址
        //附带的参数
        $oauth->setRequest($request);
        $callback_url = action('Web\WechatController@OauthRedirect');
        
        //获取当前的连接
        $current_full_url = !empty($fullurl) ? $fullurl : $request->fullUrl();
        
        $callback_fullurl = $callback_url . '?target_url=' . $current_full_url;
        
        return $oauth->redirect($callback_fullurl);
    }
    
}
