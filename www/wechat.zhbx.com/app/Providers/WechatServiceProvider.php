<?php

namespace App\Providers;

use EasyWeChat\Factory;
use Illuminate\Support\ServiceProvider;

class WechatServiceProvider extends ServiceProvider
{
    
    //开启延时加载
    protected $defer = false;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //微信服务提供者，单例
        $this->app->singleton("Wechat",function(){
            $config = config('wechat');
            print_r($config);
            $app = Factory::officialAccount($config);
            
            return $app;
        });
    }
    
    /**
     * @return array
     */
    public function provides()
    {
        return [
            //微信服务提供者
            "Wechat",
        ];
    }
}
