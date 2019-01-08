<?php

namespace App\Services;


use App\Exceptions\InvalidRequestException;
use App\Models\FieldProfile;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * @param $fee
     * @param $type
     * @param array $field_profile_id_arr
     * @return Order
     */
    public function store($fee, $type, $field_profile_id_arr = [])
    {
        $order = DB::transaction(function () use ($fee, $type, $field_profile_id_arr) {
            $user = Auth::user();
            $order = new Order([
                'total_fees' => $fee,
                'status' => Order::STATUS_PENDING,
                'type' => $type
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            foreach ($field_profile_id_arr as $v) {
                //检查场馆
                if (($a = FieldProfile::query()->where('id', $v['id'])->decrement('amount')) < 1) {
                    throw new InvalidRequestException('场馆已经被选择');
                }

                $day = week_day_map()[$v['weekday']];
                $hour = sprintf("%02d", $v['time'] + 1);//1小时过期

                $item = $order->items()->make([
                    'field_profile_id' => $v['id'],
                    'amount' => 1,
                    'fees' => $v['fees'],
                    'expires_at' => $day . ' ' . $hour . ':00:00'
                ]);
                $item->save();
            }
            return $order;
        });
        return $order;
    }

    /**
     * 获取已经过期的场地订单item
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOverdueFieldProfile()
    {
        //todo test code
        $time_start = date('Y-m-d H:i:s', strtotime('-5 day'));//minute
        $time_end = date('Y-m-d H:i:s', strtotime('+5 day'));

        return OrderItem::query()->whereBetween('expires_at', [$time_start, $time_end])->where('status', 1)->get();
    }

    /**
     * 处理已经过期的订单item
     *
     * @return bool
     */
    public function handleOverdueFieldProfile()
    {
        DB::transaction(function () {
            $orderItems = self::getOverdueFieldProfile();
            Log::info('handleOverdueFieldProfile',$orderItems->toArray());
            foreach ($orderItems as $orderItem) {
                $orderItem->status = 0;//设置为已过期
                $orderItem->save();
                FieldProfile::query()->where('id', $orderItem->field_profile_id)->update(['amount' => 1]);//场地数量设置为1，就是可以预定啦。
            }
        });
        return true;
    }

    /**
     * 获取已支付的订单
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAppliedOrder()
    {
        return Order::query()->with(['items' => function ($query) {
            $query->where('status', 1);
        }])
            ->where('status', Order::STATUS_APPLIED)
            ->get();
    }

    public function handleAppliedOrder()
    {
        $orders = self::getAppliedOrder();
        Log::info('handleAppliedOrder',$orders->toArray());
        foreach ($orders as $order) {
            if ($order->items->isEmpty()) {
                $order->status = Order::STATUS_SUCCESS;
                $order->save();
            }
        }
    }
}