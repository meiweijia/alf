<?php

namespace App\Models;

use App\Exceptions\InvalidRequestException;
use Illuminate\Database\Eloquent\Model;

class FieldProfile extends Model
{
    public function getFeesAttribute($value)
    {
        return $value / 100;
    }

    public function setFeesAttribute($value)
    {
        $this->attributes['fees'] = $value * 100;
    }

    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InvalidRequestException('场馆已经被选择');
        }

        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }


    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
