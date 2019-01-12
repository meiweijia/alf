<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\ApiController;
use App\Libraries\Wechat;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PaymentController extends ApiController
{
    public function getWechatJssdkConfig(Request $request)
    {
        $this->checkPar($request, [
            'thisurl' => 'required',
        ]);

        return app(Wechat::class)->getWechatJssdkConfig($request->input('thisurl'));
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function paymentByBalance(Request $request)
    {
        $this->checkPar($request, [
            'order_id' => 'required',
        ]);

        $order = Order::query()->find($request->input('order_id'));

        //判断订单状态
        if ($order->status != Order::STATUS_PENDING) {
            return $this->error('订单' . Order::$orderStatusMap[$order->status]);
        }

        $balance = UserProfile::query()->where('user_id')->pluck('balance');
        if ($order->total_fees > $balance) {
            return $this->error('余额不足');
        }

        //更新订单状态
        $order->update(['status' => Order::STATUS_APPLIED]);
        //扣除账户余额
        $data = UserProfile::query()->where('id', Auth::id())->decrement('balance', $order->total_fees);
        return $this->success($data, '支付成功');
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function paymentByWechat(Request $request)
    {
        $this->checkPar($request, [
            'order_id' => 'required',
        ]);

        $order = Order::query()->find($request->input('order_id'));
        //生成微信支付配置项
        $config = app(Wechat::class)->getPaymentConfig($order->no, $order->total_fees * 100, '澳莱芙支付中心-预定场馆');

        return $config ? $this->success($config) : $this->error(null, '微信支付签名验证失败');
    }

    /**
     * 微信支付通知
     *
     * @param Request $request
     * @return mixed
     */
    public function wechatPayNotify(Request $request)
    {
        $app = app('wechat.payment');

        $response = $app->handlePaidNotify(function ($message, $fail) use ($app) {
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::query()->where('no', $message['out_trade_no'])->first();

            if (!$order || $order->paid_at) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }

            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
            /// TODO 这里还没写完呢
            $wechatOrder = $app->order->queryByTransactionId($message['transaction_id']);

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {

                    if ($order->status == Order::STATUS_PENDING) {//订单状态为未支付时，才进这里的逻辑 1
                        //更新订单数据
                        $order->paid_at = time(); // 更新支付时间为当前时间
                        $order->status = Order::STATUS_APPLIED;
                        $order->payment_no = $message['transaction_id'];
                        $order->payment_method = Order::PAYMENT_TYPE_WECHAT;

                        //充值订单
                        if ($order->type == Order::ORDER_TYPE_RECHARGE) {
                            // 更新账户余额
                            UserProfile::query()->where('user_id', $order->user_id)->increment('balance', $order->total_fees);
                            //增加用户充值总额
                            $total_recharge = Redis::incrby(User::TOTAL_RECHARGE_KEY . $order->user_id, $order->total_fees);
                            // 更新会员等级
                            $level = User::calcLevel($total_recharge);//计算会员等级
                            UserProfile::query()->where('id', $order->user_id)->update(['level' => $level]);
                        }

                        // 订场订单
                        if ($order->type == Order::ORDER_TYPE_RESERVE && $order->payment_method == Order::PAYMENT_TYPE_WECHAT) {
                            // 微信支付订单，也更新会员等级
                            // 增加用户充值总额
                            $total_recharge = Redis::incrby(User::TOTAL_RECHARGE_KEY . $order->user_id, $order->total_fee);
                            $level = User::calcLevel($total_recharge);//计算会员等级
                            UserProfile::query()->where('id', $order->user_id)->update(['level' => $level]);
                        }
                    }

                } elseif (array_get($message, 'result_code') === 'FAIL') {// 用户支付失败
                    $order->status = Order::STATUS_FAILED;

                    // 订场订单 更改场馆为可预定
                    if ($order->type == Order::ORDER_TYPE_RESERVE) {
                        $order->items()->update([
                            'amount' => 1
                        ]);
                    }
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            $order->save(); // 保存订单

            return true; // 返回处理完成
        });
        return $response;
    }
}