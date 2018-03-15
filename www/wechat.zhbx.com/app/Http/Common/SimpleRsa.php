<?php

namespace App\Http\Common;

use App\Exceptions\JsonException;

/**
 * Class SimpleRsa
 * 一个普通的 rsa 类，简单的封装
 * @package App\Http\Common
 *
 */
class SimpleRsa {
    
    /**
     * 构造方法
     * @author  jianwei
     * @param $private_key  私钥
     */
    public function __construct()
    {
        
    }
    
    /**
     * 检查私钥是否可用
     * @author  jianwei
     */
    public function getResourcePrivateKey()
    {
        $private_key = $this->getPrivateKey();

        //生成Resource类型的密钥，如果密钥文件内容被破坏，openssl_pkey_get_private函数返回false
        $resource_private_key = openssl_pkey_get_private($private_key);
        
        if($resource_private_key === false){
            throw new JsonException(10100);
        }
        
        return $resource_private_key;
    }
    
    /**
     * 检查私钥是否可用
     * @author  jianwei
     */
    public function getResourcePublicKey()
    {
        $public_key = $this->getPublicKey();
        
        var_dump($public_key);
        exit();
            
        //生成Resource类型的密钥，如果密钥文件内容被破坏，openssl_pkey_get_private函数返回false
        $resource_public_key = openssl_pkey_get_public($public_key);
        
        if($resource_public_key === false){
            throw new JsonException(10101);
        }
        
        return $resource_public_key;
    }
    
    /**
     * 私玥解密
     * @author  jianwei
     * @param $encrypted    string  加密后的数据
     */
    public function decrypt($encrypted)
    {
        //获取key
        $private_key = $this->getResourcePrivateKey();

        $encrypted = base64_decode(urldecode($encrypted));

        //解密数据
        openssl_private_decrypt($encrypted,$decrypted,$private_key);
        return $decrypted;
    }
    
    /**
     * 公钥加密
     * @author  jianwei
     */
    public function encrypt($decrypted)
    {
        //获取key
        $public_key = $this->getPublicKey();
        //解密数据
        openssl_public_encrypt($decrypted,$encrypted,$public_key);
    
        return urlencode(base64_encode($encrypted));
//        return base64_encode($encrypted);
    }
    
    
    /**
     * 生成密钥
     * @author  jianwei
     * @param $private_key_bits int
     */
    /*
    public function generateKey()
    {
        //生成私钥
        
        //私钥路径
        $private_key_path = $this->getPrivateKeyPath();
        //公钥路径
        $public_key_path = $this->getPublicKeyPath();
        
        $config = array(
            "digest_alg"    => "sha512",
            "private_key_bits" => $private_key_bits,           //字节数  512 1024 2048  4096 等
            "private_key_type" => OPENSSL_KEYTYPE_RSA,   //加密类型
        );
        
        $res = openssl_pkey_new($config);
        if($res == false) return false;
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        
        file_put_contents($public_key_path,$public_key);
        file_put_contents($private_key_path,$private_key);
        
        openssl_free_key($res);
        
        return true;
    }
    */
    
    /**
     * 生成密钥
     * @author  jianwei
     */
    public function generateKey()
    {
        //生成私玥
        $this->generatePrivateKey();
        
        //生成公钥
        $this->generatePublicKey();
        
        //生成供Java使用的私钥pkcs8_private_key.pem
        $this->generatePkcs8PrivateFile();
        
        //生成证书请求文件rsaCertReq.csr
        $this->generateRsaCertReq();
    
        // 生成证书rsaCert.crt，并设置有效时间
        $this->generateRsaCert();
        
        //生成供iOS使用的公钥文件public_key.der
        $this->generateAppPublicKey();
        
        return true;
    }
    
    /**
     * 生成私玥
     * @author  jianwei
     */
    public function generatePrivateKey()
    {
        //私钥路径
        $private_key_path = $this->getPrivateKeyPath();
        
        $length = config('rsa.length');
        
        $command = self::opensslCommand().' genrsa -out '.$private_key_path.' '.$length;
        
        $res = exec($command);
        
        return $res;
    }
    
    /**
     * 生成私玥
     * @author  jianwei
     */
    public function generatePublicKey()
    {
        //私钥路径
        $private_key_path = $this->getPrivateKeyPath();
        
        //公钥路径
        $public_key_path = $this->getPublicKeyPath();
        
        //openssl rsa -in private_key.pem -out rsa_public_key.pem -pubout
        $command = self::opensslCommand().' rsa -in '.$private_key_path.' -out '.$public_key_path.' -pubout';
        
        $res = exec($command);
        
        return $res;
    }
    
    /**
     * 生成私玥
     * @author  jianwei
     */
    public function generateRsaCertReq()
    {
        //私钥路径
        $private_key_path = $this->getPrivateKeyPath();
        
        //公钥路径
        $rsa_cer_req_path = $this->getRsaCerReqPath();
        
        //openssl req -new -key private_key.pem -out rsaCerReq.csr
        $command = self::opensslCommand().' req -new -key '.$private_key_path.' -out '.$rsa_cer_req_path;
        
        $res = exec($command);
        
        return $res;
    }
    
    /**
     * 生成供Java使用的私钥pkcs8_private_key.pem
     * @author  jianwei
     */
    public function generatePkcs8PrivateFile()
    {
        //私钥路径
        $private_key_path = $this->getPrivateKeyPath();
    
        //公钥路径
        $pkcs8_private_key_path = $this->getPkcs8PrivateKeyPath();
    
        //openssl
        $command = self::opensslCommand().' pkcs8 -topk8 -in '.$private_key_path.' -out '.$pkcs8_private_key_path.' -nocrypt';
    
        $res = exec($command);
    
        return $res;
    }
    
    /**
     * 生成私玥
     * @author  jianwei
     */
    public function generateRsaCert()
    {
        //私钥路径
        $private_key_path = $this->getPrivateKeyPath();
        
        //证书请求文件rsaCertReq.csr路径
        $rsa_cer_req_path = $this->getRsaCerReqPath();
        
        //生成证书rsaCert.crt
        $rsa_cert_path = $this->getRsaCertPath();
        
        $rsa_cert_expiry = config('rsa.rsa_cert_expiry');
        
        //openssl x509 -req -days 3650 -in rsaCerReq.csr -signkey private_key.pem -out rsaCert.crt
        $command = self::opensslCommand().' x509 -req -days '.$rsa_cert_expiry.' -in '.$rsa_cer_req_path.' -signkey '.$private_key_path.' -out '.$rsa_cert_path;
        
        $res = exec($command);
        
        return $res;
    }
    
    /**
     * 生成私玥
     * @author  jianwei
     */
    public function generateAppPublicKey()
    {
        //生成证书rsaCert.crt
        $rsa_cert_path = $this->getRsaCertPath();
        
        $app_public_path = $this->getAppPublicKeyPath();
        
        //openssl x509 -outform der -in rsaCert.crt -out public_key.der
        $command = self::opensslCommand().'  x509 -outform der -in '.$rsa_cert_path.' -out '.$app_public_path;
        
        $res = exec($command);
        
        return $res;
    }
    
    /**
     * 生成csr文件
     * @author  jianwei
     */
    public function generateCsr()
    {
        //获取私玥
        $private_key = $this->getPrivateKey();
    
        //获取配置
        $dn = config('rsa.dn');
        
        $csr = openssl_csr_new($dn, $private_key);
        
        return $csr;
    }
    
    /**
     * 通过私玥生成证书请求文件rsaCertReq.csr
     * @author  jianwei
     */
//    public function generateRsaCertReq()
//    {
//        $csr = $this->generateCsr();
//
//        $csr_path = $this->getCsrPath();
//
//        openssl_csr_export_to_file($csr,$csr_path);
//
//        return $csr_path;
//    }
    
    /**
     * 获取csr文件
     * @author  jianwei
     */
    public function getCsrPath()
    {
        $csr_path = config('rsa.path.csr_path');
        
        return $csr_path;
    }
    
    /**
     * 获取csr文件
     * @author  jianwei
     */
    public function getCsr()
    {
        $csr_path = $this->getCsrPath();
        
        if(!file_exists($csr_path)){
            throw new JsonException();
        }
        
        $csr =  file_get_contents($csr_path);
        
        return $csr;
    }
    
    /**
     * 返回私玥地址
     * @author  jianwei
     */
    public function getPrivateKeyPath()
    {
        //私钥路径
        $private_key_path = config('rsa.path.private_key_path');
        return  $private_key_path;
    }
    
    /**
     * 返回私玥地址
     * @author  jianwei
     */
    public function getPublicKeyPath()
    {
        //公钥路径
        $public_key_path = config('rsa.path.public_key_path');
        
        return  $public_key_path;
    }
    
    /**
     * 返回私玥地址
     * @author  jianwei
     */
    public function getAppPublicKeyPath()
    {
        //公钥路径
        $app_public_key_path = config('rsa.path.app_public_key_path');
        
        return  $app_public_key_path;
    }
    
    /**
     * 返回rsa_cer_req_path地址
     * @author  jianwei
     */
    public function getRsaCerReqPath()
    {
        //公钥路径
        $rsa_cer_req_path = config('rsa.path.rsa_cer_req_path');
        
        return  $rsa_cer_req_path;
    }
    
    /**
     * 返回rsa_cer_req_path地址
     * @author  jianwei
     */
    public function getRsaCertPath()
    {
        //公钥路径
        $rsa_cert_path = config('rsa.path.rsa_cert_path');
        
        return  $rsa_cert_path;
    }
    
    /**
     * 返回私玥地址
     * @author  jianwei
     */
    public function getPkcs8PrivateKeyPath()
    {
        //公钥路径
        $pkcs8_private_key_path = config('rsa.path.pkcs8_private_key_path');
        
        return  $pkcs8_private_key_path;
    }
    
    /**
     * 判断公钥是否存在
     * @author  jianwei
     */
    public function checkPublicKeyExists()
    {
        $public_key_path = $this->getPublicKeyPath();
        
        if(!file_exists($public_key_path)){
            throw new JsonException(10102);
        }
        
        return $public_key_path;
    }
    
    /**
     * 判断私钥是否存在
     * @author  jianwei
     */
    public function checkPrivateKeyExists()
    {
        $private_key_path = $this->getPrivateKeyPath();
        
        if(!file_exists($private_key_path)){
            throw new JsonException(10103);
        }
        
        return $private_key_path;
    }
    
    /**
     * 返回私玥
     * @author  jianwei
     */
    public function getPrivateKey()
    {
        //私钥路径
        $private_key_path = $this->checkPrivateKeyExists();
        
        $private_key = file_get_contents($private_key_path);
        
        return  $private_key;
    }
    
    /**
     * 返回私玥
     * @author  jianwei
     */
    public function getPublicKey()
    {
        //私钥路径
        $public_key_path = $this->checkPublicKeyExists();

        $public_key = file_get_contents($public_key_path);
        
        return  $public_key;
    }
    
    
    /**
     * 用于获取openssl命令的地址
     * @author  jianwei
     */
    public static function opensslCommand()
    {
        static $command_path = null;
        if($command_path !== null){
            return $command_path;
        }
        
        $command = 'openssl';
        $command_path = Helper::checkCommandPath($command);
        
        if($command_path === false){
            throw new JsonException(10001);
        }
        
        return $command_path;
    }
    
}
