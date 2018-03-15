<?php

namespace App\Exceptions;

use Exception;

class JsonException extends Exception 
{
    /**
     * 错误码列表
     * 10000 - 19999 基本错误
     */
    protected $code_list = [
        /*----------微信相关错误----------*/
        '20000' =>  [
            'msg'   =>  '请求微信浏览器中打开！',
        ],
        '20001' =>  [
            'msg'   =>  '微信用户授权失败！',
        ],
        
        
        /*-------------用户相关错误************/
        '30000' =>  [
            'msg'   =>  '请先登录!',
        ],
        '30001' =>  [
            'msg'   =>  '没找到该用户!',
        ],
        
        /*---基本错误 end-----*/
        '10000'   =>  [
            'msg'   =>  '参数错误'
        ],
    ];


    /**
     * 构造函数
     */
    public function __construct($code, $data = [])
    {
        $this->code = $code;
        $this->data = $data;
    }


    /**
     * 获取错误信息
     */
    public function getErrorMsg()
    {
        $module = config('site.module_name');
        $re = [
            'code' => 10000,
            'msg'  => $this->code_list[10000]['msg'],
            'data' => '',
            'module'    =>  $module,
        ];
        if (empty($this->code_list[$this->code])) {
            return $re;
        }

        $re['code'] = $this->code;
        $re['msg']  = $this->code_list[$this->code]['msg'];
        $re['data'] = $this->data;
        $re['module'] = $module;

        return $re;
    }
}
