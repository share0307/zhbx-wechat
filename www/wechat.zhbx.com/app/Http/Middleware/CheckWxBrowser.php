<?php

namespace App\Http\Middleware;

use App\Exceptions\JsonException;
use App\Http\Common\Helper;
use Closure;

class CheckWxBrowser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //检查是否微信浏览器
        if (Helper::checkWxBrowser() === false) {
            throw new JsonException(20000);
        }
        
        $return_data =  $next($request);
        
        return $return_data;
    }
}
