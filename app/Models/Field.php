<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    const FIELD_TYPE_SHUTTLECOCK = 1;
    const FIELD_TYPE_BASKETBALL = 2;

    public static $typeMap = [
        self::FIELD_TYPE_SHUTTLECOCK => '羽毛球',
        self::FIELD_TYPE_BASKETBALL => '篮球',
    ];

    /**
     * 场地信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profile()
    {
        return $this->hasMany(FieldProfile::class);
    }

    public function sunday()
    {
        $this->with('profile', function ($query) {
            $query->where('weekday', 0);
        })->get();
    }
}
