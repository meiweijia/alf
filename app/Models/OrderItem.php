<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $fillable = [
        'field_profile_id',
        'amount',
        'fees',
        'expires_at',
    ];

    protected $appends = ['start_time'];

    public function getFeesAttribute($value)
    {
        return $value / 100;
    }

    public function setFeesAttribute($value)
    {
        $this->attributes['fees'] = $value * 100;
    }

    public function getStartTimeAttribute($value)
    {
        if (!isset($this->attributes['expires_at'])) return false;
        $day = Carbon::parse($this->attributes['expires_at'])->modify('-1 hour')->toDateString();
        return $day . '（' . week_map(date('w', strtotime($day))) . '）';
    }

    public function field_profile()
    {
        return $this->belongsTo(FieldProfile::class);
    }
}
