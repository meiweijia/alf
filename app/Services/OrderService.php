<?php

namespace App\Services;


use App\Exceptions\InvalidRequestException;
use App\Models\FieldProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class OrderService
{
    const RESERVE_FIELD_INFO_MSG_KEY = 'alf:reserve:field:info:msg:';

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
            if (!$user) {
                $user = User::find(0);
            }
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

            $sms_msg = '';
            $day = '';
            $count = count($field_profile_id_arr);
            foreach ($field_profile_id_arr as $k => $v) {
                $total_fees += $v['fees'];
                //检查场馆
                if ((FieldProfile::query()->where('id', $v['id'])->pluck('amount')->first()) < 1) {
                    throw new InvalidRequestException(null, '场馆已经被选择');
                }

                if ($user->id == 0) {//线下下单，直接把场馆设为不可预定
                    FieldProfile::query()->where('id', $v['id'])->update(['amount' => 0]);
                }

                $day = week_day_map()[$v['weekday']];
                $time_end = sprintf("%02d", $v['time'] + 1) . ':00:00';//1小时过期

                $item = $order->items()->make([
                    'field_profile_id' => $v['id'],
                    'amount' => 1,
                    'fees' => $v['fees'],
                    'status' => 1,
                    'expires_at' => $day . ' ' . $time_end
                ]);
                $item->save();

                $time_start = sprintf("%02d", $v['time']) . ':00:00';

                $sms_msg .= $time_start . '-' . $time_end . '（' . $v['name'] . '）' . ($k == $count - 1 ? '；' : '、');//10:00-11:00（场地1）、10:00-12:00（场地2）；
            }

            Redis::hmset(self::RESERVE_FIELD_INFO_MSG_KEY . $order->id, [
                'day' => $day,
                'msg' => $sms_msg,
                'type' => $type
            ]);


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
        $time_end = date('Y-m-d H:i:s', strtotime('+5 minute'));

        return OrderItem::query()
            ->where('expires_at', '<', $time_end)
            ->where('status', 1)
            ->get();
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

            $orders = self::getAppliedOrder();
            Log::info('handleAppliedOrder', $orders->toArray());
            foreach ($orders as $order) {
                if ($order->items->isEmpty()) {
                    $order->status = Order::STATUS_SUCCESS;
                    $order->save();
                }
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