<?php

namespace App\Http\Middleware;

use App\Exceptions\JsonException;
use App\Http\Business\WechatOauthBusiness;
use Closure;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CheckWxLogin
{
    private $wechat_oauth_business = null;
    
    /**
     * 构造方法
     * @author  jianwei
     */
    public function __construct(WechatOauthBusiness $wechat_oauth_business)
    {
        $this->wechat_oauth_business = $wechat_oauth_business;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //检查是否微信登录
        $check = $this->checkWechatLogin($request);

        if ($check instanceof RedirectResponse) {
            return $check;
        }
        
        $return_data = $next($request);
        
        return $return_data;
    }
    
    /**
     * 检查是否已经微信登录的业务
     * @author  jianwei
     */
    private function checkWechatLogin($request)
    {
        try{
            $this->wechat_oauth_business->checkWechatLogin();
            var_dump('已经登录了！');
        }catch (JsonException $e){
            if ($e->getCode() == '30000'){
                //跳转到登录页面
                $oauth = app('Wechat')->oauth;
                //需要用户授权取得详细信息
                //$oauth->scopes(['snsapi_userinfo']);
                //静默授权
                $oauth->scopes(['snsapi_base']);
                $oauth->setRequest($request);
                return $oauth->redirect();
            }
    
        }
        
        
        return null;
    }
    
}
