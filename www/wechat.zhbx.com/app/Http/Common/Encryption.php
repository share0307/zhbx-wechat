<?php

namespace App\Http\Common;
use App\Exceptions\JsonException;
use Illuminate\Support\Facades\Validator;

/**
 * Class Encryption
 * 加密方法封装
 * @package App\Http\Common
 */
class Encryption
{
    
    /**
     * @param $params 参数
     * @param $key  string  加密的key
     * @param $except_arr  array 排除的字段
     * @return string
     */
    public static function genSign($params, $key,array $except_arr = [])
    {
        //去掉一些为空的参数
        //$params = array_filter($params);
        ksort($params);

        $tmpstr = '';
        $trim_str = "'\t\n\r \v";
        foreach ($params as $k => $v) {
            //为null的字段不参与加密
            if (!in_array($k, $except_arr) && $v !== null) {
                //$tmpstr .= $k . '=' . $v . '&';
                //@TODO 微信android客户端会把'带到链接参数上面
                $tmpstr .= Helper::trimAny($k, $trim_str) . '=' . Helper::trimAny($v, $trim_str) . '&';
            }
        }
        $tmpstr .= '_key=' . $key;
        $sign = strtoupper(md5($tmpstr));
        return $sign;
    }
    
    /**
     * 用于过滤器判断是否合法
     * @author  jianwei
     * @param $param    array   请求的参数
     */
    public static function checkSign(array $param)
    {
        $rule = array(
            '_ts'   =>  ['required'],
            '_rd'   =>  ['required',],
            '_terminal'   =>  ['required',],
            '_sign'   =>  ['required',],
        );
        $validate = Validator::make($param, $rule);
        if($validate->fails()){
            throw new JsonException(10200);
        }
        $client_sign = $param['_sign'];
        
        //_ts:timestamp,时间戳
        //_rd:随机值
        //_sign:加密后的参数
        $except_arr = array('_sign');
        
        //服务器的签名
        $sign_key = config('sign.rand_key');
        $server_sign = self::genSign($param,$sign_key,$except_arr);
        
        if($server_sign != $client_sign){
            throw new JsonException(10201);
        }
        
        return true;
    }
    
    /**
     * 获取加密后的参数
     * @author  jianwei
     * @param $param    array   查询
     */
    public static function getSign(array $param)
    {
        $rule = array(
            '_ts'   =>  ['required','integer',],
            '_rd'   =>  ['required',],
            '_sign'   =>  ['required',],
        );
        
        $validate = Validator::make($param, $rule);
        
        if($validate->fails()){
            throw new JsonException(10200);
        }
        
        $client_sign = $param['_sign'];
        
        //_ts:timestamp,时间戳
        //_rd:随机值
        //_sign:加密后的参数
        $except_arr = array('_sign');
        
        //服务器的签名
        $sign_key = config('sign.rand_key');
        //$server_sign = self::genSign($param,$sign_key,$except_arr);
        $server_sign = self::genSign2($param,$sign_key,$except_arr);
        
        return $server_sign;
    }
    
    /**
     * @param $params
     * @param $key
     * @param array $except_arr
     * @return string返回加密的各参数，只是用于给前端对比，会删掉
     */
    public static function genSign2($params, $key,array $except_arr = [])
    {
        ksort($params);
        $tmpstr = '';
        $trim_str = "'\t\n\r \v";
        foreach ($params as $k => $v) {
            if (!in_array($k, $except_arr) && $v !== null) {
                //$tmpstr .= $k . '=' . $v . '&';
                //@TODO 微信android客户端会把'带到链接参数上面
                $tmpstr .= Helper::trimAny($k, $trim_str) . '=' . Helper::trimAny($v, $trim_str) . '&';
            }
        }
        $tmpstr .= '_key=' . $key;
        $sign = strtoupper(md5($tmpstr));
        
        $response_arr = array();
        $response_arr['sign'] = $sign;
        $response_arr['sign_str'] = $tmpstr;
        
        return $response_arr;
    }
    
}
