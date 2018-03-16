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
        try {
            $this->wechat_oauth_business->checkWechatIsLogin();
        }catch (JsonException $e){
            try{
                $this->checkWechatIsLogin();
            }catch (JsonException $e) {
                if ($e->getCode() == '30000') {
                    //检查是否微信登录
                    $check = $this->wechat_oauth_business->WechatOauthLogin($request, 'snsapi_base');
        
                    if ($check instanceof RedirectResponse) {
                        return $check;
                    }
                }
            }
        }
        
        $return_data = $next($request);
        
        return $return_data;
    }
    
    
}
