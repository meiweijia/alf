<?php

namespace App;

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
    const LOGIN_TYPE_PASSWORD = 1;
    const LOGIN_TYPE_CODE = 2;

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

    public function findForPassport($login)
    {
        return $this->where('mobile_no', $login)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        return $password == $this->password || Hash::check($password, $this->password);
    }
}
