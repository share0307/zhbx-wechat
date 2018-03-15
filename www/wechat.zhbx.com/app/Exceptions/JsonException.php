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
        
        /*---基本错误 end-----*/
        '10000'   =>  [
            'msg'   =>  '参数错误'
        ],
        '10001'   =>  [
            'msg'   =>  '命令不存在!'
        ],
        '10002'   =>  [
            'msg'   =>  '请输入正确的手机号码！'
        ],
        '10003'   =>  [
            'msg'   =>  '获取手机验证码类型有误!'
        ],
        '10100'   =>  [
            'msg'   =>  '私钥不可用!'
        ],
        '10101'   =>  [
            'msg'   =>  '公钥不可用!'
        ],
        '10102'   =>  [
            'msg'   =>  '公钥不存在!'
        ],
        '10103'   =>  [
            'msg'   =>  '私钥不存在!'
        ],
        '10104'   =>  [
            'msg'   =>  'APP公钥不存在!'
        ],
        '10105'   =>  [
            'msg'   =>  'APP公钥不可用!'
        ],
        '10200'   =>  [
            'msg'   =>  '加密参数异常!',
            'status_code'   =>  403
        ],
        '10201'   =>  [
            'msg'   =>  '加密校验失败!',
        ],
        '10300'   =>  [
            'msg'   =>  '获取接口地址失败!'
        ],
        '10301'   =>  [
            'msg'   =>  '接口请求失败!'
        ],
        '10302'   =>  [
            'msg'   =>  '请求异常!'
        ],
        '10400'   =>  [
            'msg'   =>  '文章预览密钥校验失败!'
        ],
    
    
        //请求专用,方便之后迁移到包
        '100000'   =>  [
            'msg'   =>  '获取接口地址失败!'
        ],
        '100001'  =>  [
            'msg'   =>  '请求接口失败,无法请求接口!',
        ],
        '100002'  =>  [
            'msg'   =>  '请求接口异常，接口非正常响应!',
        ],
        '100003'  =>  [
            'msg'   =>  '接口返回数据参数异常!',
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
        $re = [
            'code' => 10000,
            'msg'  => $this->code_list[10000]['msg'],
            'data' => '',
            'module'    =>  config('site.module_name'),
        ];
        if (empty($this->code_list[$this->code])) {
            return $re;
        }

        $re['code'] = $this->code;
        $re['msg']  = $this->code_list[$this->code]['msg'];
        $re['data'] = $this->data;
        $re['module'] = config('site.module_name');

        return $re;
    }
}
