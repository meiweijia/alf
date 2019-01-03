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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(FieldProfile::class);
    }
}
