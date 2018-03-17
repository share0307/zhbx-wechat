<?php

namespace App\Http\Business\Dao;

use App\Exceptions\JsonException;

class UsersDao extends DaoBase{

    /**
     * 存储用户数据
     * @author  jianwei
     * @param $user_data    array   用户数据
     */
    public function SaveUserInfo(array $user_data)
    {
        $rules = [
            'openid'    =>  ['required',],
            'nickname'  =>  ['required',],
            'avatar'  =>  ['required',],
            'email'  =>  [],
            'sex'  =>  [],
            'sex'  =>  [],
        ];
    
        $message = array();
//        $message = [
////            'subscribe.required'            => '是否订阅不能为空',
//            'openid.required'               => 'openid不能为空',
//        ];
    
        $validate = app('validator')->make($user_data, $rules, $message);
        
        if($validate->fails()){
            throw new JsonException(10000,$validate->messages());
        }
        
        $UsersModel = app('UserModel');
        //原表总已经有 username，但是 username=openid，这里先做个保留
        $UsersModel->username = $user_data['openid'];
        //原表中有 pwd 字段，但是此字段貌似是没什么意义的，此处置为 ''
        $UsersModel->pwd = '';
        $UsersModel->openid = $user_data['openid'];
        $UsersModel->nickname = $user_data['nickname'];
        $UsersModel->avatar = $user_data['avatar'];
        $UsersModel->avatar = $user_data['avatar'];
        
    }

    
    /**
     * 通过 openid 获取一个用户数据
     * @author  jianwei
     * @param $openid   string  微信用户 openid
     */
    public function GetUserInfo($openid, array $columns = ['*'])
    {
        if(empty($openid)){
            throw new JsonException(10000);
        }
        
        
        $UsersModel = app('UsersModel');
        
        $query = $UsersModel->select($columns);
    
        $query->OpenIdQuery();
        
        $user_info = $query->first();
        
        if(isset($user_info->id)){
            throw new JsonException(30001);
        }
        
        return $user_info;
    }

}
