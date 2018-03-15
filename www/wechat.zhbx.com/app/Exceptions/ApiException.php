<?php

namespace App\Exceptions;

use Exception;

/**
 * Class ApiException
 * api抛出的错误直接返回
 * @package App\Exceptions
 */
class ApiException extends Exception{
    
    /**
     * 保存api中返回的错误的数组
     * @author  jianwei
     */
    private $err_data = array();
    
    /**
     * 构造方法，获取错误数据
     * @author  jianwei
     * @param   $err_data   直接把
     */
    public function __construct(array $err_data)
    {
        $this->err_data = $err_data;
        $this->code = isset($err_data['code']) ? $err_data['code'] : 0;
    }
    
    /**
     * 获取错误信息
     */
    public function getErrorMsg()
    {
        return $this->err_data;
    }
}
