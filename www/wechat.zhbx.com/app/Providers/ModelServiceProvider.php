<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    //开启延时加载
    protected $defer = true;
    
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
        // 用户模型
        $this->app->bind('UsersModel',\App\Http\Model\Users::class);
    }
    
    /**
     * @return array
     */
    public function provides()
    {
        return [
            // 用户模型
            "UsersModel",
        ];
    }
}
