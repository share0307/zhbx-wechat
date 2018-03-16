<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    /**
     * 软删除
     */
    use SoftDeletes;
    
    /**
     * @var string
     */
    protected $table = 'user';
    
    /**
     * 通过 openid 查询
     * @author  jianwei
     * @param $openid   string  微信的 openid
     */
    public function scopeOpenIdQuery($openid)
    {
        return $this->where('openid',$openid);
    }
    
    
}