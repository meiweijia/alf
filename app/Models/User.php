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

    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mobile_no', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 重写登录字段
     *
     * @param $login
     * @return mixed
     */
    public function findForPassport($login)
    {
        return $this->where('mobile_no', $login)->first();
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
}
