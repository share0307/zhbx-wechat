<?php

namespace App\Http\Controllers\Web;

class HomeController extends WebBaseController{
    
    /**
     * 默认首页
     * @author jianwei
     */
    public function index()
    {
        return 'hello world!';
    }
    

}
