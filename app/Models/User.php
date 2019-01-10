<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @package App
 */
class User extends Authenticatable
{
    const LOGIN_TYPE_PASSWORD = 1;//密码登录
    const LOGIN_TYPE_CODE = 2;//手机号+验证码登录

    const TOTAL_RECHARGE_KEY = 'user:recharge:';

    const USER_LEVEL_GEN = 1;
    const USER_LEVEL_SILVER = 2;
    const USER_LEVEL_GOLD = 3;
    const USER_LEVEL_DIAMOND = 4;

    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile_no',
        'password',
        'openid',
        'nickname',
        'sex',
        'language',
        'city',
        'province',
        'country',
        'headimgurl',
        'unionid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * 重写登录字段
     *
     * @param $username
     * @return mixed
     */
    public function findForPassport($username)
    {
        return $this->orWhere('openid', $username)->orWhere('mobile_no', $username)->first();
    }

    /**
     * 重写密码验证
     *
     * @param $password
     * @return bool
     */
    public function validateForPassportPasswordGrant($password)
    {
        return $password == $this->password || Hash::check($password, $this->password);
    }

    /**
     * 用户信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * 用户订单 包含预定场地和充值的
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 计算等级
     *
     * @param $total_fees
     * @return int
     */
    public static function calcLevel($total_fees)
    {
        if ($total_fees > 2000) return self::USER_LEVEL_SILVER;
        if ($total_fees > 5000) return self::USER_LEVEL_GOLD;
        if ($total_fees > 10000) return self::USER_LEVEL_DIAMOND;
        return self::USER_LEVEL_GEN;
    }
}
