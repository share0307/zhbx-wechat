<?php

namespace App\Http\Business\Api;

use App\Exceptions\ApiException;
use App\Exceptions\JsonException;
use App\Http\Common\Encryption;
use App\Http\Common\Helper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class DemoBase
 * @package App\Http\Api
 * @author  jianwei
 * @created at  2016-7-31
 * Api 基类
 */
abstract class ApiBase {
    
    
    /**
     * 检查是否需要加密
     * @author  jianwei
     */
    abstract function checkSign();
    
    /**
     * 获取该接口的密钥
     * @author  jianwei
     */
    abstract function getSignKey();
    
    /**
     * 用于接口返回格式是json的请求
     * @author  jianwei
     * @param $api  url 接口地址
     * @param $params    参数
     * @param $method   请求方式
     * @param $config   配置
     * @param $options  请求的其他参数
     * @return array
     */
    protected function jsRequest($api,array $params = [],$method = 'post',array $config = [], $options = [])
    {
        //当为需要加密，那么就加密咯..
        if($this->checkSign()){
            $params['_ts'] = Helper::getNow();
            $params['_rd'] = Helper::randomStr(8);
            //$params['_terminal'] = Helper::getUserSource();
            $params['_sign'] = Encryption::genSign($params, $this->getSignKey(), ['_sign']);
        }

//        $divide = is_numeric(strpos($api,'?')) ? '&' : '?';
//        $full_api = $api.$divide.http_build_query($params);
//        var_dump($full_api);
//        dd($params);
        
        //强制注入参数，为了兼容..
        $options['params'] = $params;
        
        $result = $this->sendRequest($api,$options,$method,$config);
        
        $data = Helper::js_decode($result);
        
        return $data;
    }
    
    
    /**
     * 校验接口返回是否正常，仅仅是对用内部项目的 code 是否为0判断！
     * @author  jianwei
     * @param $api_data array api 返回的数据
     */
    protected function checkApiResponse($api_data)
    {
        if(!is_array($api_data)) {
            throw new JsonException(10000);
        }
        
        if(!isset($api_data['code']) || !isset($api_data['data'])){
            throw new JsonException(100003);
        }
        
        if($api_data['code'] != 0){
            throw new ApiException($api_data);
        }
        
        return $api_data['data'];
    }
    
    
    /**
     * 请求
     * @author  jianwei
     * @param $api  string  接口地址
     * @param $params  array    参数
     * @param $config   一些配置参数，比如说 timeout(请求时间)
     * @param $method   请求方式
     * @param $options  请求的其他参数
     */
    protected function sendRequest($api, array $options = array(), $method = 'post', $config = [])
    {
        //判断是否链接
        if(!filter_var($api,FILTER_VALIDATE_URL)){
            throw new JsonException(100000);
        }
        
        //请求超时时间
        if(!isset($config['timeout'])){
            $config['timeout'] = 10;
        }elseif(isset($config['timeout']) && $config['timeout'] <= 0){
            unset($config['timeout']);
        }
        
        //初始化
//        $GuzzleHttp = new Client($config);
        $GuzzleHttp = app('GuzzleHttp\Client', $config);
        
        //参数整理
        if (app()->environment() == 'local') {
            // @TODO 强制使用 ip4
            // 系统会先查询 ip6 地址，再查询 ip4
            // 这种情况会导致请求极其缓慢
            $options['curl'] = [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ];
        }
        
        //post,put的data
        if (in_array($method,['post','put',]) && !isset($options['form_params']) && isset($options['params'])) {
            $options['form_params'] = $options['params'];
            unset($options['params']);
        }
        
        //get 等其他参数
        if (in_array($method,['get','delete','head','options','patch',]) && !isset($options['query']) && isset($options['params'])) {
            $options['query'] = $options['params'];
            unset($options['params']);
        }
        
        try {
            $response = $GuzzleHttp->request($method, $api, $options);
        }catch (ClientException $e){
            throw new JsonException(100002,['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
        }catch (ConnectException $e){
            throw new JsonException(100001,['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
        }catch (RequestException $e){
            throw new JsonException(100001,['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
        }
        
        //返回码判断
        if ($response->getStatusCode() > 299 || $response->getStatusCode() < 199) {
            throw new JsonException(100002,['code'=>$response->getStatusCode(),'msg'=>$response->getState(),]);
        }
        
        
        //获取请求结果
        $result = $response->getBody()->getContents();
        
        return $result;
    }
    
}
