<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Libraries\Wechat;
use App\Models\FieldProfile;
use App\Models\UserProfile;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{
    /**
     * 订单
     *
     * @param Request $request
     * @param OrderService $orderService
     * @return Response
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function reserve(Request $request, OrderService $orderService)
    {
        $this->checkPar($request, [
            'fees' => 'required',
            'fields' => 'required',
        ]);

        $fees = $request->input('fees');

        $fields = $request->input('fields');

        $field_arr = json_decode($fields, true);

        //创建订单
        $order = $orderService->store($fees, Order::ORDER_TYPE_RESERVE, $field_arr);

        //订单创建完成  把账户余额带出来
        $balance = UserProfile::query()->where('user_id', Auth::id())->pluck('balance')->first();

        $order->balance = $balance;

        return $this->success($order);
    }

    /**
     * 充值
     *
     * @param Request $request
     * @param OrderService $orderService
     * @return Response
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function recharge(Request $request, OrderService $orderService)
    {
        $this->checkPar($request, [
            'fees' => 'required',
        ]);

        $fees = $request->input('fees');

        //创建订单
        $order = $orderService->store($fees, Order::ORDER_TYPE_RECHARGE);

        //生成微信支付配置项
        $config = app(Wechat::class)->getPaymentConfig($order->no, $fees * 100, '澳莱芙支付中心-余额充值');

        return $config ? $this->success($config) : $this->error(null, '微信支付签名验证失败');
    }


    public function reserveWithAdmin(Request $request, OrderService $orderService)
    {
        $this->checkPar($request, [
            'fees' => 'required',
            'fields' => 'required',
        ]);

        $fees = $request->input('fees');

        $fields = $request->input('fields');

        $field_arr = json_decode($fields, true);

        //创建订单
        $order = $orderService->store($fees, Order::ORDER_TYPE_RESERVE, $field_arr);

        return $this->success($order,'预定成功！');
    }

    /**
     * 获取充值记录
     *
     * @param Request $request
     * @return Response
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
     * @return Response
     */
    public function getReserveLogs(Request $request)
    {
        $perPage = $request->input('per_page') ?? 10;


        $data = Order::query()->where('user_id', Auth::id())
            ->with([
                'items:order_id,field_profile_id',
                'items.field_profile:id,field_id',
                'items.field_profile.field:id,name,type'
            ])
            ->select('id', 'status', 'total_fees', 'created_at')
            ->where('type', Order::ORDER_TYPE_RESERVE)
            ->whereIn('status', [
                Order::STATUS_SUCCESS,
                Order::STATUS_APPLIED])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->success($this->paginate($data));
    }

    /**
     * 获取 order 表的记录
     *
     * @param $type
     * @param int $perPage
     * @return array
     */
    private function getOrder($type, $perPage = 10)
    {
        $user_id = Auth::id();
        $data = Order::query()->where('user_id', $user_id)
            ->where('type', $type)
            ->where('status', Order::STATUS_APPLIED)
            ->orderByDesc('created_at')
            ->paginate($perPage);
        return $this->paginate($data);
    }

    /**
     * 订单详情
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function getOrderDetail(Request $request)
    {
        $this->checkPar($request, [
            'order_id' => 'required',
        ]);
        return Order::query()->with([
            'items',
            'items.field_profile',
            'items.field_profile.field:id,name,type'
        ])->find($request->input('order_id'));
    }
}
