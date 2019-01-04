<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{

    /**
     * 获取充值记录
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBalanceLogs(Request $request)
    {
        $user_id = Auth::id();
        $data = User::with(['order' => function ($query) {
            $query->where('type', Order::ORDER_TYPE_RECHARGE);
        }])->where('user_id', $user_id)
            ->get();
        return $this->success($data);
    }

    /**
     * 获取订单记录
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOrderLogs(Request $request)
    {
        $user_id = Auth::id();
        $data = User::with([
                'order' => function ($query) {
                    $query->where('type', Order::ORDER_TYPE_RESERVE);
                },
                'order.items'
            ]
        )->where('user_id', $user_id)
            ->get();
        return $this->success($data);
    }
}
