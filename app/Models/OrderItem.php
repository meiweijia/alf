<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $fillable = [
        'field_profile_id',
        'amount',
        'fees',
        'expires_at',
    ];

    public function getFeesAttribute($value)
    {
        return $value / 100;
    }

    public function setFeesAttribute($value)
    {
        return $value * 100;
    }

    public function field_profile()
    {
        return $this->belongsTo(FieldProfile::class);
    }
}
