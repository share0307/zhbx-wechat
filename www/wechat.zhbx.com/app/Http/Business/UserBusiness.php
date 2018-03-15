<?php

namespace App\Http\Business;

use App\Exceptions\JsonException;
use App\Http\Business\Dao\UsersDao;

class UserBusiness extends BusinessBase{
    
    //用户模型
    protected $users_dao = null;
    
    /**
     * 构造方法
     * @author  jianwei
     */
    public function __construct(UsersDao $users_dao)
    {
        $this->users_dao = $users_dao;
    }
    
    
    /**
     * 同步用户数据
     * @author  jianwei
     * @param $user_data    array   用户数据，暂时为从微信总获取的用户数据
     */
    function SyncUserInfoToDb(array $user_data)
    {
        if(!isset($user_data['id'])){
            throw new JsonException(10000);
        }
        
        //先通过 openid 查找此 openid 用户是否已经存在
        //存在则更新，不存在则创建！
        try{
            $user_details = $this->users_dao->GetUserInfo($user_data['openid']);
            //更新数据
        }catch (JsonException $e){
            //插入数据
            $this->users_dao->SaveUserInfo($user_data);
        }
        
        return $user_details;
    }
    
}
