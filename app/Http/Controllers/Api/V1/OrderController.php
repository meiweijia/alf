<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\FieldProfile;
use App\Models\OrderItem;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{

    public function reserve(Request $request)
    {
        $user_id = Auth::id();

        $pay_method = '';
        //创建订单
        $order = $this->create();

        //订单场地信息写入
        OrderItem::query()->insert([
            'order_id' => $order->id,
            'order_id' => $order->id,
            'order_id' => $order->id,
            'order_id' => $order->id,
        ]);

        //预定时，把场地数量改为0，不再接受预定
        FieldProfile::query()->where('id', '')->update(['amount' => 0]);

        //订单创建完成  把账户余额带出来
        $balance = UserProfile::query()->where('user_id')->pluck('balance');

    }

    public function recharge(Request $request)
    {
        $user_id = Auth::id();

        $amount = $request->input('amount');

        //创建订单
        $order = $this->create();

        //支付成功更新账户余额
        User::query()->where('user_id', $user_id)->increment('balance', $amount);

        //累计充值 修改会员等级
    }

    /**
     * 创建订单
     *
     * @return Order
     */
    public function create()
    {
        $user_id = Auth::id();

    }

    /**
     * 获取充值记录
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBalanceLogs(Request $request)
    {
        $prePage = $request->input('per_page') ?? 10;
        $data = $this->getOrder(Order::ORDER_TYPE_RECHARGE, $prePage);
        return $this->success($data);
    }

    /**
     * 获取订场记录
     *
     * @param Request $request
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getReserveLogs(Request $request)
    {
        $prePage = $request->input('per_page') ?? 10;
        $data = $this->getOrder(Order::ORDER_TYPE_RESERVE, $prePage);
        return $this->success($data);
    }

    private function getOrder($type, $perPage = 10)
    {
        $user_id = Auth::id();
        $data = Order::query()->where('user_id', $user_id)
            ->where('type', $type)
            ->paginate($perPage);
        return $this->paginate($data);
    }
}
