<?php

namespace App\Services;


use App\Exceptions\InvalidRequestException;
use App\Models\FieldProfile;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
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

            $total_fees = 0;

            foreach ($field_profile_id_arr as $v) {
                $total_fees += $v['fees'];
                //检查场馆
                if ((FieldProfile::query()->where('id', $v['id'])->pluck('amount')->first()) < 1) {
                    throw new InvalidRequestException('场馆已经被选择');
                }

                $day = week_day_map()[$v['weekday']];
                $hour = sprintf("%02d", $v['time'] + 1);//1小时过期

                $item = $order->items()->make([
                    'field_profile_id' => $v['id'],
                    'amount' => 1,
                    'fees' => $v['fees'],
                    'status' => 1,
                    'expires_at' => $day . ' ' . $hour . ':00:00'
                ]);
                $item->save();
            }
            if ($type == Order::ORDER_TYPE_RESERVE) {
                $order->update([
                    'total_fees' => $total_fees
                ]);
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
        $time_start = date('Y-m-d H:i:s', strtotime('-1 minute'));//minute
        $time_end = date('Y-m-d H:i:s', strtotime('+1 minute'));

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
            Log::info('handleOverdueFieldProfile', $orderItems->toArray());
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

    /**
     * 处理已经支付的订单，如果订单下所有的预定场地都已经过期，则把订单设为已完成状态
     */
    public function handleAppliedOrder()
    {
        $orders = self::getAppliedOrder();
        Log::info('handleAppliedOrder', $orders->toArray());
        foreach ($orders as $order) {
            if ($order->items->isEmpty()) {
                $order->status = Order::STATUS_SUCCESS;
                $order->save();
            }
        }
    }


    public function getFailedOrder()
    {
        $time_start = date('Y-m-d H:i:s', strtotime('-5 minute'));//5分钟未支付，订单失效
        return Order::query()
            ->where('created_at', '<=', $time_start)
            ->where('status', Order::STATUS_PENDING)
            ->get();
    }

    public function handleFailedOrder()
    {
        $orders = self::getFailedOrder();
        foreach ($orders as $order) {
            $order->update(['status' => Order::STATUS_FAILED]);
            $order->items->each(function ($item) {
                $item->update(['status' => 0]);
                FieldProfile::query()->where('id', $item->field_profile_id)->update(['amount' => 1]);//场地数量设置为1，就是可以预定啦。
            });
        }
    }
}