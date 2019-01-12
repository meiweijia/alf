<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'level',
        'balance'
    ];

    public function getBalanceAttribute($value)
    {
        return $value / 100;
    }

    public function setBalanceAttribute($value)
    {
        return $value * 100;
    }
}
