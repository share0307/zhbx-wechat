<?php

namespace App\Http\Common;

use App\Exceptions\JsonException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;

class Helper
{
    /**
     * 分页处理
     *
     * @author jilin
     * @param array $data
     * @param array $options
     * @return array|LengthAwarePaginator
     */
    public static function createPaginator(array $data = [], array $options = [])
    {
        if (isset($data['data']) && isset($data['total']) && isset($data['per_page']) && isset($data['current_page'])) {
            $paginator = new LengthAwarePaginator(
                $data['data'],
                $data['total'],
                $data['per_page'],
                $data['current_page'],
                $options
            );
            return $paginator;
        } else {
            throw new JsonException(10302);
        }
    }

    /**
     * 密码加密
     * @param $password 需要加密的密码
     * @param $random 随机数字，空时会自动生成对应的随机数字
     * @return array ['encrypt' => xxx, 'random' => xxx]
     * @created 2016-07-27
     * @auth chentengfeng
     */
    public static function passwordEncrypt($password, $random='')
    {
        if (empty($random)) {
            $random = self::randomStr(10);
            return ['encrypt' => substr(md5($password . $random), 0, -2), 'random' => $random];
        }
        
        return substr(md5($password . $random), 0, -2);
    }
    
    /**
     * 生成一串随机字母
     * @param $length //输出的字符串长度
     * @return string
     * @created 2016-07-27
     * @auth chentengfeng
     * @update -> 添加数字随机数 -> weixinhua 2016-07-29
     */
    public static function randomStr($length,$numeric = false)
    {
        $string = $numeric == false ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ':'123456789';
        
        $str = '';
        mt_srand(10000000*(double)microtime());
        
        for($i = 0,$str_len = strlen($string)-1 ; $i < $length; $i++) {
            $str .= $string[mt_rand(0, $str_len)];
        }
        
        return $str;
    }
    
    /**
     * 获取随机密码
     * @return string
     */
    public static function queryRandomPassword($length, $chars = '01234567890123456789')
    {
        $length = is_numeric($length) && $length > 0 ? $length : 6;
        
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
    
    /**
     * 生成手机验证码
     * @param $length int 长度，默认为4
     * @param $chars    随机种子
     */
    public static function genrateSmsCode($length = 4,$chars = '123456789123456789')
    {
        $chars = Helper::trimAny($chars);
        if(!is_integer($length) || $length < 0 || empty($chars)){
            throw new JsonException(10000);
        }
        
        $verification_code = Helper::queryRandomPassword($length, $chars);
        
        return $verification_code;
    }
    
    /*
     * curl_get提交方式
     * @param string $url 请求链接
     * @oaram int $req_number 失败请求次数
     * @param int $timeout 请求时间
     *
     */
    public static function curlGet($url, $req_number = 2, $timeout=30) {
        
        //防止因网络原因而高层无法获取
        $cnt = 0;
        $result = FALSE;
        while ( $cnt < $req_number && $result === FALSE) {
            $cnt++;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            //禁止直接显示获取的内容 重要
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //在发起连接前等待的时间，如果设置为0，则无限等待。
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            //不验证证书下同
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //SSL验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch); //获取
            curl_close($ch);
        }//end func curl_get
        
        //获取数据
        $data = $result ? $result : null;
        
        return $data;
    }//end func curlGet
    
    /**
     * curl_get提交方式
     * @param string $url 请求链接
     * @param array $post_data 请求数据
     * @param string $post_type 请求类型(json)
     *
     */
    public static function curlPost($url, $post_data = '', $post_type = '', $curl_params = [])
    {
        //初始化curl
        $ch = curl_init();
        //设置请求地址
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置curl参数，要求结果是否输出到屏幕上，为true的时候是不返回到网页中
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        //https ssl 验证
        if (!empty($curl_params['ssl'])) {
            $ssl = $curl_params['ssl'];
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //验证站点名
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // 只信任CA颁布的证书
            if (!empty($ssl['sslca'])) {
                curl_setopt($ch, CURLOPT_CAINFO, $ssl['sslca']);
            }
            if (!empty($ssl['sslcert'])) {
                curl_setopt($ch, CURLOPT_SSLCERT, $ssl['sslcert']);
            }
            if ($ssl['sslkey']) {
                curl_setopt($ch, CURLOPT_SSLKEY, $ssl['sslkey']);
            }
        } else {
            //验证站点名
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //是否验证https(当请求链接为https时自动验证，强制为false)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 只信任CA颁布的证书
        }
        
        //设置post提交方式
        curl_setopt($ch, CURLOPT_POST, 1);
        //设置post字段
        $post_data = is_array($post_data) ? http_build_query($post_data) : $post_data;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        
        //判断是否json提交
        if ('json' == $post_type) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Expect:',
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($post_data))
            );
        }
        
        //运行curl
        $output = curl_exec($ch);
        //关闭curl
        curl_close($ch);
        //返回结果
        return $output;
    }//end func curlPost
    
    /**
     * @author  jianwei
     * 加密,生成加密后的字符串以及解密密钥,
     * @param   $token string access_token
     * @param   $key   string 约定的key值
     * @param   $type  int 0为加密，1为解密
     * @notic   其实并不会太安全,只是作为简单的加密处理
     */
    public static function encrypt($token,$key,$type = 0){
        if(empty($token)){
            return false;
        }
        //$key = sha1($key);
        if(!$type){
            //加密
            if(empty($key) || mb_strlen($token) > mb_strlen($key)){
                //足够长的随机种子
                $key = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_';
            }
            
            $encrypt_str = base64_encode($token);
            
            //生成随机key
            $key = substr(str_shuffle($key),0,strlen($encrypt_str));
            
            $sign_token = $encrypt_str ^ $key;
            
            return ['token'=>$sign_token,'key'=>$key];
        }
        return base64_decode($token ^ $key);
    }
    
    /**
     * 字典加密
     * @param $hashtable    需要加密的数组
     * @param $secret       安全密钥
     * @param bool $qhs
     * @return string
     */
    public static function sign($hashtable, $secret)
    {
        //"g56ef@4f%df$%hyU*"
        // 第一步：把字典按Key的字母顺序排序
        ksort($hashtable);
        $str = $secret;
        // 第二步：把所有参数名和参数值串在一起
        foreach($hashtable as $key => $value){
            $str .= $key.$value;
        }
        
        // 第三步：使用MD5加密
        $sign = md5($str);
        return strtoupper($sign);
    }

    /**
     * 过滤字符串 && 数字 && 数组 && 对象的空格
     * @author  jianwei
     * @param   需要过滤的数据
     * @param   $charlist = " \t\n\r\0\x0B",过滤的模式
     * @notic   支持多维
     */
    public static function trimAny(&$data, $charlist = " \t\n\r\0\x0B")
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = self::trimAny($value, $charlist);
                } else {
                    $data[$key] = self::trimAny($value, $charlist);
                }
            }
        } else if (is_string($data)) {
            $data = trim($data, $charlist);
        }
        return $data;
    }

    /*
     * 重新封装json解码函数,可防止因为编码而造成得解码失败
     * @param   $json   string      json字符串
     * @param   $coding array     把什么编码转成UTF-8
     * @notice:暂时此json格式化工具函数只支持utf-8的解码
     * @author  jianwei
     */
    public static function js_decode($json,array $coding = [])
    {
        //去除空格
        $json = static::trimAny($json);
        if(!is_string($json) || empty($json)){
            //return $json;
            return array();
        }
        //检查当前编码是否utf-8等
        $encoding = 'UTF-8';
        if(!mb_check_encoding($json,$encoding)){
            //不管如何,都默认存在以下几种编码的转码
            $coding = array_merge(['ASCII,UTF-8','ISO-8859-1'],$coding);
            $coding_str = implode(',',$coding);
            $json = mb_convert_encoding($json, $encoding, $coding_str);
            //移除BOM头,否则json_decode失败
            if (substr($json, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) {
                $json = substr($json, 3);
            }
        }
        $decode_data = (array)json_decode($json,1);
        if(empty($decode_data)){
            return [];
        }
        return $decode_data;
    }
    
    /**
     * 获取当前时间
     * @author  jianwei
     * @param   $flag   double  当$flag 为 true 时,等同于 time()
     */
    public static function getNow($flag = false)
    {
        static $now_time = null;
        if(null === $now_time){
            $now_time = date('YmdHis',time());
        }
        
        if(true === $flag){
            return date('YmdHis',time());
        }
        
        return $now_time;
    }
    
    /**
     * 格式化时间
     * @author  jianwei
     * @param   $time   int     需要格式化的时间
     * @param   $format_temp    string      格式化的格式
     */
    public static function formatTime($time,$format_temp = 'Y-m-d H:i:s')
    {
        $timestamp = (int)strtotime($time);
        
        return date($format_temp,$timestamp);
    }
    
    /**
     * 获取格式化时间
     * @author  jianwei
     */
    public static function getFormatTime($format = 'Y-m-d H:i:s',$flag = false)
    {
        static $now_time = null;
        if(null === $now_time){
            $now_time = date($format,time());
        }
    
        if(true === $flag){
            return date($format,time());
        }
    
        return $now_time;
    }
    
    /**
     * 检查手机号码是否合法
     * @author  jianwei
     */
    public static function checkMobile($mobile)
    {
        $partten = '/^1[34578]{1}\d{9}$/';
        return preg_match($partten,$mobile,$matches);
    }
    
    /**
     * 获取数组中的数字
     * @author  jianwei
     */
    public static function ArrayFilterNum(array $arr = [])
    {
        $arr = Helper::trimAny($arr);
        $filter_num_func = function($val){
            return is_numeric($val);
        };
        
        return array_filter($arr,$filter_num_func);
    }
    
    
    /**
     * 检查某个数组中是否有重复数据
     * @author  jianwei
     * @param   $arr    array   数组
     */
    public static function checkArrRepeat(array $arr)
    {
        return max(array_count_values($arr)) > 1 ? true : false;
    }
    
    
    
    /**
     * 保留2位小数点
     * @author  jianwei
     */
    public static function sprint2f($num)
    {
        return sprintf('%01.4f',$num);
    }
    
    /**
     * 获取命令行的地址，仅仅用于linux
     * @author  jianwei
     * @param $command  string      命令名称
     */
    public static function checkCommandPath($command)
    {
        //检查命令
        $command_path = exec('which  ' . $command);
        //$command_path = substr_replace($command_path, '', 0, mb_strlen($command));
    
        if (empty($command_path)) {
            return false;
        }
    
        $command_path = Helper::trimAny($command_path, " \t\n\r:");
    
        return $command_path;
    }
    
    /**
     * 生成请求参数字符串
     * @param $params   array
     */
    private static function assemble(array $params)
    {
        $path = '';
        foreach ($params as $key => $value) {
            $path .= $key . '=' . $value . '&';
        }
        
        return trim($path, '&');
    }
    
    
    
    /**
     * 获取用户来源
     * @author  jianwei
     */
    public static function getUserSource()
    {
        return app('request')->get('_terminal');
    }
    
    
    /**
     * 生成一个相对比较唯一的uid
     * @param $namespace    string  namespace
     */
    public static function create_guid($namespace = '') {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= \Illuminate\Support\Facades\Request::server('REQUEST_TIME');
        $data .= \Illuminate\Support\Facades\Request::server('HTTP_USER_AGENT');
        $data .= \Illuminate\Support\Facades\Request::server('SERVER_ADDR');
        $data .= \Illuminate\Support\Facades\Request::server('SERVER_PORT');
        $data .= \Illuminate\Support\Facades\Request::server('REMOTE_ADDR');
        $data .= \Illuminate\Support\Facades\Request::server('REMOTE_PORT');
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash, 0, 8) .
            '-' .
            substr($hash, 8, 4) .
            '-' .
            substr($hash, 12, 4) .
            '-' .
            substr($hash, 16, 4) .
            '-' .
            substr($hash, 20, 12);
        
        return $guid;
    }
    

    /**
     * 清除用户登陆session cookie
     * author hxc
     */
    public static function removeUserInfo() {
        //清除用户session
        //self::removeUserSession();
        //清除用户cookie
        self::removeUserCookie();
        return true;
    }



    /**
     * 生成唯一 id,比 guid 稍短一些
     * @author  jianwei
     */
    public static function uniqid()
    {
        return md5(uniqid());
    }
    
    /**
     * 日志版本号
     * @author  jianwei
     * @param $build    boolean 是否创建版本
     * @notice:当为 true 是才生成一个新的版本，否则返回旧的版本号
     */
    public static function logVersion($build = false)
    {
        //不是话一个版本
        static $version = null;
        
        if($build == true){
            $version = self::uniqid();
        }
        
        return $version;
    }

    /**
     * 日志的频道
     * @author  jianwei
     * @param $channel  string  频道的名称
     */
    public static function logChannel($channel = null)
    {
        static $channel_name = '';
        
        if(!empty($channel)){
            $channel_name = $channel;
        }
        
        return $channel_name;
    }

    /**
     * 把配置包装成js形式返回
     * @author  jianwei
     */
    public static function buildJsConfig(array $arr_config, $param_name = 'conf')
    {
        if (empty($arr_config)) {
            throw new JsonException(10000);
        }

        $param_name = ($param_name === 'conf') ? 'conf' : $param_name;

        $config_json = json_encode($arr_config, true);

        $js_code = <<<js_code
        var {$param_name} = $config_json;
js_code;

        return $js_code;
    }



    /**
     * 隐藏手机号码
     * @author jilin
     * @param $mobile
     */
    public static function hidMobile($mobile, $char = '*', $char_length = 5)
    {
        if(!self::checkMobile($mobile))
        {
            return $mobile;
        }

        $chars = str_pad('', $char_length, $char);
        $mobile = substr_replace($mobile, $chars, 3, 5);

        return $mobile;
    }

    /**
     * 隐藏QQ号码
     * @author jilin
     * @param $qq
     */
    public static function hidQQ($qq, $char = '*', $char_length = 5)
    {
        if(empty($qq) || !is_numeric($qq) || strlen($qq)<1)
        {
            return $qq;
        }

        $chars = str_pad('', $char_length, $char);
        if (strlen($qq)>8){
            $qq = substr($qq, 0, 2).$chars.substr($qq, strlen($qq)-2, 2);
        } elseif (strlen($qq)<=3) {
            $qq = substr_replace($qq, $char, 1, 1);
        } else {
            $qq = substr($qq, 0, 1). $chars. substr($qq, strlen($qq)-1, 1);
        }
        return $qq;
    }

    /**
     * 隐藏邮件
     * @author jilin
     * @param $email
     */
    public static function hidEmail($email, $char = '*', $char_length = 5)
    {
        if(!self::checkEmail($email))
        {
            return $email;
        }

        $chars = str_pad('', $char_length, $char);
        $arr = explode('@', $email);
        $pre = $arr[0];

        if (strlen($pre)>8){
            $pre = substr($pre, 0, 2).$chars.substr($pre, strlen($pre)-2, 2);
        } elseif (strlen($pre)<=3) {
            $pre = substr_replace($pre, $char, 1, 1);
        } else {
            $pre = substr($pre, 0, 1). $chars. substr($pre, strlen($pre)-1, 1);
        }

        $email = $pre.'@'.$arr[1];

        return $email;
    }

    public static function hidUserName($name, $char = '*', $char_length = 5)
    {
        if(empty($name) || strlen($name)<1)
        {
            return $name;
        }

        $chars = str_pad('', $char_length, $char);
        if (mb_strlen($name)>8){
            $name = mb_substr($name, 0, 2).$chars.mb_substr($name, mb_strlen($name)-2, 2);
        } elseif (mb_strlen($name)<=3) {
            $name = mb_substr($name, 0, 1). $char;
        } else {
            $name = mb_substr($name, 0, 1). $chars. mb_substr($name, strlen($name)-1, 1);
        }

        return $name;
    }

    /**
     * 获取默认头像
     *
     * @author jilin
     */
    public static function getUserAvatar($avatar)
    {
        if (isset($avatar) && empty($avatar)) {
            $real_avatar = \App\Http\Common\Helper::asset_resource('/static/user_avatar.png','images');
        } else {
            $real_avatar = Helper::forceSchema($avatar);
        }
        return $real_avatar;
    }

    /**
     * 格式化数字
     *
     * @author Tang3
     * @param int  $num 待处理数字
     * @param int  $float 保留小数位
     * @param bool $signe 正数是否添加+号
     * @return int|string
     * @date   2017-07-14
     */
    public static function processNumber($num, $float = 2, $signe = false)
    {
        $num = number_format($num, $float, '.', '');

        if ($signe) {
            $num = $num > 0 ? '+'.$num : $num;
        }

        return $num;
    }

    /**
     * 获取股票价格，用于计算
     * 优先获取今收价格，然后现价，最后昨收价
     *
     * @author Tang3
     * @param $info
     * @return int
     * @date   2017-07-18
     */
    public static function stockCalculPrice($info)
    {
        $price = ($info['close'] ?? 0) > 0 ? $info['close'] : (($info['price'] ?? 0) > 0) ? $info['price'] : ($info['close'] ?? 0);

        return $price;
    }

    /**
     * 个位转换 万、亿
     * @author jingchang
     * @param $num double 数字
     * @param int $precision 精度
     */
    public static function conversionUnit($num, $precision = 2)
    {
        $a = 10000;
        $b = 100000000;
        $abs_num = abs($num);

        if ($abs_num >= $a && $abs_num < $b){
            return round((float)$num / $a, $precision).'万';
        } elseif ($abs_num >= $b) {
            return round((float)$num / $b, $precision).'亿';
        } else {
            return round((float)$num, $precision);
        }
    }

    /**
     * 股票交易量显示处理
     * ≥5位数后面单位为万手，≥9位数后面单位为亿手
     *
     * @author Tang3
     * @param $volume
     * @return string
     * @date   2017-07-21
     */
    public static function processVolume($volume)
    {
        $volume = intval($volume) / 100;

        if ($volume >= 100000000) {
            $volume = self::processNumber(round($volume / 100000000, 2), 2) . '亿';
        } elseif ($volume >= 10000) {
            $volume = self::processNumber(round($volume / 10000, 2), 2) . '万';
        } elseif ($volume > 0) {
            $volume = self::processNumber(round($volume, 2), 2);
        } else {
            $volume = '0.00';
        }

        return $volume;
    }

    /**
     * 股票交易额显示处理
     * 默认单位为万，≥9位数后面单位为亿
     *
     * @author Tang3
     * @param $amount
     * @return string
     * @date   2017-07-21
     */
    public static function processAmount($amount)
    {
        $amount = intval($amount);

        if ($amount >= 100000000) {
            $amount = self::processNumber(round($amount / 100000000, 2), 2) . '亿';
        } elseif($amount >= 10000) {
            $amount = self::processNumber(round($amount / 10000, 2), 2) . '万';
        } elseif($amount > 0) {
            $amount = self::processNumber(round($amount, 2), 2);
        } else {
            $amount = '0.00';
        }

        return $amount;
    }

    /**
     * 格式化跳转链接
     * @author jingchang
     * @param $url
     */
    public static function formatUrl($url)
    {
        if (!preg_match("/^(http|https):/", $url)) {
            $url = 'http://'.$url;
        }

        return $url;
    }

    

    /**
     * 返回真实code
     *
     * @author Tang3
     * @param $code
     * @return mixed
     * @date   2017-08-11
     */
    public static function getRealCode($code)
    {
        $search = ['SH', 'SZ', 'CT', 'sh', 'sz', 'ct'];

        return str_replace($search, '', $code);
    }

    /**
     * 获取source
     * author hxc
     * @param $code
     * @return string
     */
    public static function getSource($code)
    {
        preg_match("/[a-zA-Z]+/", $code, $source);
        if(!empty($source[0])) {
            return $source[0];
        }
        return '';
    }

    /**
     * 修改网址协议为自动匹配 //
     *
     * @author Tang3
     * @param $url string
     * @return string
     * @date   date
     */
    public static function forceSchema($url, $absolute = false)
    {
        $host = \Request::getSchemeAndHttpHost();
        if ($absolute == true || strpos($url, $host) === false) {
            $url = str_replace(['http:', 'https:'], '', $url);
        } else {
            $url = str_replace($host, '', $url);
        }
        
        return $url;
    }

    /**
     * 替换表情
     *
     * @author jilin
     * @param $str
     * @return string
     */
    public static function replaceEmotion($str)
    {
        $arr = array(
            '[兔子]' => "<img src=".self::asset_resource('/static/emotion/001.gif', 'images').">",
            '[熊猫]' => "<img src=".self::asset_resource('/static/emotion/002.gif', 'images').">",
            '[给力]' => "<img src=".self::asset_resource('/static/emotion/003.gif', 'images').">",
            '[神马]' => "<img src=".self::asset_resource('/static/emotion/004.gif', 'images').">",
            '[浮云]' => "<img src=".self::asset_resource('/static/emotion/005.gif', 'images').">",
            '[织]' => "<img src=".self::asset_resource('/static/emotion/006.gif', 'images').">",
            '[围观]' => "<img src=".self::asset_resource('/static/emotion/007.gif', 'images').">",
            '[威武]' => "<img src=".self::asset_resource('/static/emotion/008.gif', 'images').">",
            '[嘻嘻]' => "<img src=".self::asset_resource('/static/emotion/009.gif', 'images').">",
            '[哈哈]' => "<img src=".self::asset_resource('/static/emotion/010.gif', 'images').">",
            '[爱你]' => "<img src=".self::asset_resource('/static/emotion/011.gif', 'images').">",
            '[晕]' => "<img src=".self::asset_resource('/static/emotion/012.gif', 'images').">",
            '[泪]' => "<img src=".self::asset_resource('/static/emotion/013.gif', 'images').">",
            '[馋嘴]' => "<img src=".self::asset_resource('/static/emotion/014.gif', 'images').">",
            '[抓狂]' => "<img src=".self::asset_resource('/static/emotion/015.gif', 'images').">",
            '[哼]' => "<img src=".self::asset_resource('/static/emotion/016.gif', 'images').">",
            '[可爱]' => "<img src=".self::asset_resource('/static/emotion/017.gif', 'images').">",
            '[怒]' => "<img src=".self::asset_resource('/static/emotion/018.gif', 'images').">",
            '[汗]' => "<img src=".self::asset_resource('/static/emotion/019.gif', 'images').">",
            '[呵呵]' => "<img src=".self::asset_resource('/static/emotion/020.gif', 'images').">",
            '[睡觉]' => "<img src=".self::asset_resource('/static/emotion/021.gif', 'images').">",
            '[钱]' => "<img src=".self::asset_resource('/static/emotion/022.gif', 'images').">",
            '[偷笑]' => "<img src=".self::asset_resource('/static/emotion/023.gif', 'images').">",
            '[酷]' => "<img src=".self::asset_resource('/static/emotion/024.gif', 'images').">",
            '[衰]' => "<img src=".self::asset_resource('/static/emotion/025.gif', 'images').">",
            '[吃惊]' => "<img src=".self::asset_resource('/static/emotion/026.gif', 'images').">",
            '[闭嘴]' => "<img src=".self::asset_resource('/static/emotion/027.gif', 'images').">",
            '[鄙视]' => "<img src=".self::asset_resource('/static/emotion/028.gif', 'images').">",
            '[挖鼻屎]' => "<img src=".self::asset_resource('/static/emotion/029.gif', 'images').">",
            '[花心]' => "<img src=".self::asset_resource('/static/emotion/030.gif', 'images').">",
            '[鼓掌]' => "<img src=".self::asset_resource('/static/emotion/031.gif', 'images').">",
            '[失望]' => "<img src=".self::asset_resource('/static/emotion/032.gif', 'images').">",
            '[帅]' => "<img src=".self::asset_resource('/static/emotion/033.gif', 'images').">",
            '[照相机]' => "<img src=".self::asset_resource('/static/emotion/034.gif', 'images').">",
            '[落叶]' => "<img src=".self::asset_resource('/static/emotion/035.gif', 'images').">",
            '[汽车]' => "<img src=".self::asset_resource('/static/emotion/036.gif', 'images').">",
            '[飞机]' => "<img src=".self::asset_resource('/static/emotion/037.gif', 'images').">",
            '[爱心传递]' => "<img src=".self::asset_resource('/static/emotion/038.gif', 'images').">",
            '[奥特曼]' => "<img src=".self::asset_resource('/static/emotion/039.gif', 'images').">",
            '[实习]' => "<img src=".self::asset_resource('/static/emotion/040.gif', 'images').">",
            '[思考]' => "<img src=".self::asset_resource('/static/emotion/041.gif', 'images').">",
            '[生病]' => "<img src=".self::asset_resource('/static/emotion/042.gif', 'images').">",
            '[亲亲]' => "<img src=".self::asset_resource('/static/emotion/043.gif', 'images').">",
            '[怒骂]' => "<img src=".self::asset_resource('/static/emotion/044.gif', 'images').">",
            '[太开心]' => "<img src=".self::asset_resource('/static/emotion/045.gif', 'images').">",
            '[懒得理你]' => "<img src=".self::asset_resource('/static/emotion/046.gif', 'images').">",
            '[右哼哼]' => "<img src=".self::asset_resource('/static/emotion/047.gif', 'images').">",
            '[左哼哼]' => "<img src=".self::asset_resource('/static/emotion/048.gif', 'images').">",
            '[嘘]' => "<img src=".self::asset_resource('/static/emotion/049.gif', 'images').">",
            '[委屈]' => "<img src=".self::asset_resource('/static/emotion/050.gif', 'images').">",
            '[吐]' => "<img src=".self::asset_resource('/static/emotion/051.gif', 'images').">",
            '[可怜]' => "<img src=".self::asset_resource('/static/emotion/052.gif', 'images').">",
            '[打哈气]' => "<img src=".self::asset_resource('/static/emotion/053.gif', 'images').">",
            '[顶]' => "<img src=".self::asset_resource('/static/emotion/054.gif', 'images').">",
            '[疑问]' => "<img src=".self::asset_resource('/static/emotion/055.gif', 'images').">",
            '[做鬼脸]' => "<img src=".self::asset_resource('/static/emotion/056.gif', 'images').">",
            '[害羞]' => "<img src=".self::asset_resource('/static/emotion/057.gif', 'images').">",
            '[书呆子]' => "<img src=".self::asset_resource('/static/emotion/058.gif', 'images').">",
            '[困]' => "<img src=".self::asset_resource('/static/emotion/059.gif', 'images').">",
            '[悲伤]' => "<img src=".self::asset_resource('/static/emotion/060.gif', 'images').">",
            '[感冒]' => "<img src=".self::asset_resource('/static/emotion/061.gif', 'images').">",
            '[拜拜]' => "<img src=".self::asset_resource('/static/emotion/062.gif', 'images').">",
            '[黑线]' => "<img src=".self::asset_resource('/static/emotion/063.gif', 'images').">",
            '[不要]' => "<img src=".self::asset_resource('/static/emotion/064.gif', 'images').">",
            '[good]' => "<img src=".self::asset_resource('/static/emotion/065.gif', 'images').">",
            '[弱]' => "<img src=".self::asset_resource('/static/emotion/066.gif', 'images').">",
            '[ok]' => "<img src=".self::asset_resource('/static/emotion/067.gif', 'images').">",
            '[赞]' => "<img src=".self::asset_resource('/static/emotion/068.gif', 'images').">",
            '[来]' => "<img src=".self::asset_resource('/static/emotion/069.gif', 'images').">",
            '[耶]' => "<img src=".self::asset_resource('/static/emotion/070.gif', 'images').">",
            '[haha]' => "<img src=".self::asset_resource('/static/emotion/071.gif', 'images').">",
            '[拳头]' => "<img src=".self::asset_resource('/static/emotion/072.gif', 'images').">",
            '[最差]' => "<img src=".self::asset_resource('/static/emotion/073.gif', 'images').">",
            '[握手]' => "<img src=".self::asset_resource('/static/emotion/074.gif', 'images').">",
            '[心]' => "<img src=".self::asset_resource('/static/emotion/075.gif', 'images').">",
            '[伤心]' => "<img src=".self::asset_resource('/static/emotion/076.gif', 'images').">",
            '[猪头]' => "<img src=".self::asset_resource('/static/emotion/077.gif', 'images').">",
            '[咖啡]' => "<img src=".self::asset_resource('/static/emotion/078.gif', 'images').">",
            '[话筒]' => "<img src=".self::asset_resource('/static/emotion/079.gif', 'images').">",
            '[月亮]' => "<img src=".self::asset_resource('/static/emotion/080.gif', 'images').">",
            '[太阳]' => "<img src=".self::asset_resource('/static/emotion/081.gif', 'images').">",
            '[干杯]' => "<img src=".self::asset_resource('/static/emotion/082.gif', 'images').">",
            '[萌]' => "<img src=".self::asset_resource('/static/emotion/083.gif', 'images').">",
            '[礼物]' => "<img src=".self::asset_resource('/static/emotion/084.gif', 'images').">",
            '[互粉]' => "<img src=".self::asset_resource('/static/emotion/085.gif', 'images').">",
            '[蜡烛]' => "<img src=".self::asset_resource('/static/emotion/086.gif', 'images').">",
            '[绿丝带]' => "<img src=".self::asset_resource('/static/emotion/087.gif', 'images').">",
            '[沙尘暴]' => "<img src=".self::asset_resource('/static/emotion/088.gif', 'images').">",
            '[钟]' => "<img src=".self::asset_resource('/static/emotion/089.gif', 'images').">",
            '[自行车]' => "<img src=".self::asset_resource('/static/emotion/090.gif', 'images').">",
            '[蛋糕]' => "<img src=".self::asset_resource('/static/emotion/091.gif', 'images').">",
            '[围脖]' => "<img src=".self::asset_resource('/static/emotion/092.gif', 'images').">",
            '[手套]' => "<img src=".self::asset_resource('/static/emotion/093.gif', 'images').">",
            '[雪]' => "<img src=".self::asset_resource('/static/emotion/094.gif', 'images').">",
            '[雪人]' => "<img src=".self::asset_resource('/static/emotion/095.gif', 'images').">",
            '[温暖帽子]' => "<img src=".self::asset_resource('/static/emotion/096.gif', 'images').">",
            '[微风]' => "<img src=".self::asset_resource('/static/emotion/097.gif', 'images').">",
            '[足球]' => "<img src=".self::asset_resource('/static/emotion/098.gif', 'images').">",
            '[电影]' => "<img src=".self::asset_resource('/static/emotion/099.gif', 'images').">",
            '[风扇]' => "<img src=".self::asset_resource('/static/emotion/100.gif', 'images').">",
            '[鲜花]' => "<img src=".self::asset_resource('/static/emotion/101.gif', 'images').">",
            '[喜]' => "<img src=".self::asset_resource('/static/emotion/102.gif', 'images').">",
            '[手机]' => "<img src=".self::asset_resource('/static/emotion/103.gif', 'images').">",
            '[音乐]' => "<img src=".self::asset_resource('/static/emotion/104.gif', 'images').">",
            '[送花]' => "<img src=".self::asset_resource('/static/emotion/songhua.gif', 'images').">",
            '[送茶]' => "<img src=".self::asset_resource('/static/emotion/songcha.gif', 'images').">",
            '[掌声]' => "<img src=".self::asset_resource('/static/emotion/zhangsheng.gif', 'images').">",
            '[很给力]' => "<img src=".self::asset_resource('/static/emotion/geili.gif', 'images').">",
            '[顶起]' => "<img src=".self::asset_resource('/static/emotion/dingqi.gif', 'images').">",
            '[点赞]' => "<img src=".self::asset_resource('/static/emotion/zan.gif', 'images').">",
        );

        foreach ($arr as $preg => $img) {
            $str =str_replace($preg, $img, $str);
        }

        return $str;
    }

    /**
     * 替换表情
     *
     * @author jilin
     * @param $str
     * @return string

    public static function replaceEmotion($str)
    {
        return preg_replace_array([
            '/(\[兔子\])/','/(\[熊猫\])/','/(\[给力\])/','/(\[神马\])/','/(\[浮云\])/','/(\[织\])/','/(\[围观\])/','/(\[威武\])/','/(\[嘻嘻\])/','/(\[哈哈\])/','/(\[爱你\])/','/(\[晕\])/','/(\[泪\])/','/(\[馋嘴\])/','/(\[抓狂\])/','/(\[哼\])/','/(\[可爱\])/','/(\[怒\])/','/(\[汗\])/','/(\[呵呵\])/','/(\[睡觉\])/','/(\[钱\])/','/(\[偷笑\])/','/(\[酷\])/','/(\[衰\])/','/(\[吃惊\])/','/(\[闭嘴\])/','/(\[鄙视\])/','/(\[挖鼻屎\])/','/(\[花心\])/','/(\[鼓掌\])/','/(\[失望\])/','/(\[帅\])/','/(\[照相机\])/','/(\[落叶\])/','/(\[汽车\])/','/(\[飞机\])/','/(\[爱心传递\])/','/(\[奥特曼\])/','/(\[实习\])/','/(\[思考\])/','/(\[生病\])/','/(\[亲亲\])/','/(\[怒骂\])/','/(\[太开心\])/','/(\[懒得理你\])/','/(\[右哼哼\])/','/(\[左哼哼\])/','/(\[嘘\])/','/(\[委屈\])/','/(\[吐\])/','/(\[可怜\])/','/(\[打哈气\])/','/(\[顶\])/','/(\[疑问\])/','/(\[做鬼脸\])/','/(\[害羞\])/','/(\[书呆子\])/','/(\[困\])/','/(\[悲伤\])/','/(\[感冒\])/','/(\[拜拜\])/','/(\[黑线\])/','/(\[不要\])/','/(\[good\])/','/(\[弱\])/','/(\[ok\])/','/(\[赞\])/','/(\[来\])/','/(\[耶\])/','/(\[haha\])/','/(\[拳头\])/','/(\[最差\])/','/(\[握手\])/','/(\[心\])/','/(\[伤心\])/','/(\[猪头\])/','/(\[咖啡\])/','/(\[话筒\])/','/(\[月亮\])/','/(\[太阳\])/','/(\[干杯\])/','/(\[萌\])/','/(\[礼物\])/','/(\[互粉\])/','/(\[蜡烛\])/','/(\[绿丝带\])/','/(\[沙尘暴\])/','/(\[钟\])/','/(\[自行车\])/','/(\[蛋糕\])/','/(\[围脖\])/','/(\[手套\])/','/(\[雪\])/','/(\[雪人\])/','/(\[温暖帽子\])/','/(\[微风\])/','/(\[足球\])/','/(\[电影\])/','/(\[风扇\])/','/(\[鲜花\])/','/(\[喜\])/','/(\[手机\])/','/(\[音乐\])/','/(\[送花\])/','/(\[送茶\])/','/(\[掌声\])/','/(\[很给力\])/','/(\[顶起\])/','/(\[点赞\])/',
        ], [

            "<img src='/static/emotion/001.gif'>","<img src='/static/emotion/002.gif'>","<img src='/static/emotion/003.gif'>","<img src='/static/emotion/004.gif'>","<img src='/static/emotion/005.gif'>","<img src='/static/emotion/006.gif'>","<img src='/static/emotion/007.gif'>","<img src='/static/emotion/008.gif'>","<img src='/static/emotion/009.gif'>","<img src='/static/emotion/010.gif'>","<img src='/static/emotion/011.gif'>","<img src='/static/emotion/012.gif'>","<img src='/static/emotion/013.gif'>","<img src='/static/emotion/014.gif'>","<img src='/static/emotion/015.gif'>","<img src='/static/emotion/016.gif'>","<img src='/static/emotion/017.gif'>","<img src='/static/emotion/018.gif'>","<img src='/static/emotion/019.gif'>","<img src='/static/emotion/020.gif'>","<img src='/static/emotion/021.gif'>","<img src='/static/emotion/022.gif'>","<img src='/static/emotion/023.gif'>","<img src='/static/emotion/024.gif'>","<img src='/static/emotion/025.gif'>","<img src='/static/emotion/026.gif'>","<img src='/static/emotion/027.gif'>","<img src='/static/emotion/028.gif'>","<img src='/static/emotion/029.gif'>","<img src='/static/emotion/030.gif'>","<img src='/static/emotion/031.gif'>","<img src='/static/emotion/032.gif'>","<img src='/static/emotion/033.gif'>","<img src='/static/emotion/034.gif'>","<img src='/static/emotion/035.gif'>","<img src='/static/emotion/036.gif'>","<img src='/static/emotion/037.gif'>","<img src='/static/emotion/038.gif'>","<img src='/static/emotion/039.gif'>","<img src='/static/emotion/040.gif'>","<img src='/static/emotion/041.gif'>","<img src='/static/emotion/042.gif'>","<img src='/static/emotion/043.gif'>","<img src='/static/emotion/044.gif'>","<img src='/static/emotion/045.gif'>","<img src='/static/emotion/046.gif'>","<img src='/static/emotion/047.gif'>","<img src='/static/emotion/048.gif'>","<img src='/static/emotion/049.gif'>","<img src='/static/emotion/050.gif'>","<img src='/static/emotion/051.gif'>","<img src='/static/emotion/052.gif'>","<img src='/static/emotion/053.gif'>","<img src='/static/emotion/054.gif'>","<img src='/static/emotion/055.gif'>","<img src='/static/emotion/056.gif'>","<img src='/static/emotion/057.gif'>","<img src='/static/emotion/058.gif'>","<img src='/static/emotion/059.gif'>","<img src='/static/emotion/060.gif'>","<img src='/static/emotion/061.gif'>","<img src='/static/emotion/062.gif'>","<img src='/static/emotion/063.gif'>","<img src='/static/emotion/064.gif'>","<img src='/static/emotion/065.gif'>","<img src='/static/emotion/066.gif'>","<img src='/static/emotion/067.gif'>","<img src='/static/emotion/068.gif'>","<img src='/static/emotion/069.gif'>","<img src='/static/emotion/070.gif'>","<img src='/static/emotion/071.gif'>","<img src='/static/emotion/072.gif'>","<img src='/static/emotion/073.gif'>","<img src='/static/emotion/074.gif'>","<img src='/static/emotion/075.gif'>","<img src='/static/emotion/076.gif'>","<img src='/static/emotion/077.gif'>","<img src='/static/emotion/078.gif'>","<img src='/static/emotion/079.gif'>","<img src='/static/emotion/080.gif'>","<img src='/static/emotion/081.gif'>","<img src='/static/emotion/082.gif'>","<img src='/static/emotion/083.gif'>","<img src='/static/emotion/084.gif'>","<img src='/static/emotion/085.gif'>","<img src='/static/emotion/086.gif'>","<img src='/static/emotion/087.gif'>","<img src='/static/emotion/088.gif'>","<img src='/static/emotion/089.gif'>","<img src='/static/emotion/090.gif'>","<img src='/static/emotion/091.gif'>","<img src='/static/emotion/092.gif'>","<img src='/static/emotion/093.gif'>","<img src='/static/emotion/094.gif'>","<img src='/static/emotion/095.gif'>","<img src='/static/emotion/096.gif'>","<img src='/static/emotion/097.gif'>","<img src='/static/emotion/098.gif'>","<img src='/static/emotion/099.gif'>","<img src='/static/emotion/100.gif'>","<img src='/static/emotion/101.gif'>","<img src='/static/emotion/102.gif'>","<img src='/static/emotion/103.gif'>","<img src='/static/emotion/104.gif'>","<img src='/static/emotion/songhua.gif'>","<img src='/static/emotion/songcha.gif'>","<img src='/static/emotion/zhangsheng.gif'>","<img src='/static/emotion/geili.gif'>","<img src='/static/emotion/dingqi.gif'>","<img src='/static/emotion/zan.gif'>",
        ], $str);
    }*/
    
    /**
     * 检查是否微信浏览器
     * @author  jianwei
     */
    public static function checkWxBrowser()
    {
        if (strpos(app('request')->header('user-agent'), 'MicroMessenger') === false) {
            return false;
        }
        
        return true;
    }
}
