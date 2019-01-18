<?php

namespace App\Http\Controllers\Api\V1;


use App\Exceptions\InvalidRequestException;
use App\Facades\EasySms;
use App\Http\Controllers\Api\ApiController;
use App\Libraries\Wechat;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

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

        $data = DB::transaction(function () use ($request) {
            $order = Order::query()->find($request->input('order_id'));

            //判断订单状态
            if ($order->status != Order::STATUS_PENDING) {
                throw new InvalidRequestException(null, '订单' . Order::$orderStatusMap[$order->status]);
            }

            $balance = UserProfile::query()->where('user_id', Auth::id())->pluck('balance')->first();

            if ($order->total_fees > $balance) {
                return $this->error('余额不足');
            }

            //更新订单状态
            $order->update([
                'status' => Order::STATUS_APPLIED,
                'paid_at' => time(),
                'payment_method' => Order::PAYMENT_TYPE_BALANCE,
            ]);

            $order->items->each(function ($item) {
                $item->field_profile->update(['amount' => 0]);
            });

            //扣除账户余额
            return UserProfile::query()->where('user_id', Auth::id())->decrement('balance', $order->total_fees * 100);
        });

        $order = Order::query()->find($request->input('order_id'));
        //订场成功后，发送短信或者微信模板消息
        $day = Redis::hget(OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id, 'day');
        $msg = Redis::hget(OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id, 'msg');
        $type = Redis::hget(OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id, 'type');
        //用完了就删掉，删除redis中的信息
        Redis::del([OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id]);
        $field_info = '日期为' . $day . '，时段' . $msg;

        $user = User::query()->where('id', $order->user_id)->first();
        $temp_id = 's3hPD_voLenSUxpnsgh8o68TiAcLbFZEdaRlSQMrKjI';
        $data = [
            'first' => '尊敬的' . $user->nickname . '，您的订场已成功。',
            'keyword1' => '深圳澳莱芙球馆',//场馆名称
            'keyword2' => '室内' . $type == 1 ? '羽毛球' : '篮球',//消费项目
            'keyword3' => $field_info,//场地信息
            'keyword4' => $order->total_fees,//付款金额
            'remark' => '澳莱芙球馆感谢您的惠顾！',
        ];
        app(Wechat::class)->sendTempMsg($user->openid, $temp_id, $data);


        //如果绑定了手机号，发个短信通知下
        try{
            if ($user && $user->mobile_no) {
                $result = EasySms::send($user->mobile_no, [
                    'template' => 'SMS_156280001',
                    'data' => [
                        'name' => $user->nickname,
                        'activity' => '室内' . $type == 1 ? '羽毛球' : '篮球',
                        'date' => $day,
                        'time' => $msg
                    ],
                ]);
                Log::info('sms_code_result', $result);
            }
        }catch (NoGatewayAvailableException $exception){
            Log::info('sms_send_err',$exception->getExceptions());
        }

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
                            $total_fee = 0;
                            switch ($message['total_fee']) {
                                case 20000:
                                    $total_fee = 21000;
                                    break;
                                case 30000:
                                    $total_fee = 32000;
                                    break;
                                case 50000:
                                    $total_fee = 55000;
                                    break;
                                case 100000:
                                    $total_fee = 115000;
                                    break;
                            }
                            UserProfile::query()->where('user_id', $order->user_id)->increment('balance', $total_fee);
                            //增加用户充值总额
                            $total_recharge = Redis::incrby(User::TOTAL_RECHARGE_KEY . $order->user_id, $message['total_fee']);
                            // 更新会员等级
                            $level = User::calcLevel($total_recharge);//计算会员等级
                            UserProfile::query()->where('user_id', $order->user_id)->update(['level' => $level]);
                            //充值后，余额是多少，发送短信或者微信模板消息
                            $balance = UserProfile::query()->where('user_id', 2)->pluck('balance')->first();
                            $temp_id = '4qwW1_8doikHcI_pbiAk3lc69e80uSrScb_JhzjP_Bg';
                            $data = [
                                'first' => '您好，您的余额充值已成功！',
                                'keyword1' => $message['total_fee'],//充值金额
                                'keyword2' => $message['time_end'],//充值时间
                                'keyword3' => '微信支付',//充值方式
                                'keyword4' => $balance,//当前余额
                                'remark' => '澳莱芙球馆感谢您的惠顾！',
                            ];
                            app(Wechat::class)->sendTempMsg($message['openid'], $temp_id, $data);

                            //如果绑定了手机号，发个短信通知下
                            $user = User::query()->where('id', $order->user_id)->first();
                            if ($user && $user->mobile_no) {
                                $result = EasySms::send($user->mobile_no, [
                                    'template' => 'SMS_156280288',
                                    'data' => [
                                        'money' => $message['total_fee'],
                                        'name' => $user->nickname,
                                        'balance' => $balance
                                    ],
                                ]);
                                Log::info('sms_code_result', $result);
                            }
                        }

                        // 订场订单
                        if ($order->type == Order::ORDER_TYPE_RESERVE && $order->payment_method == Order::PAYMENT_TYPE_WECHAT) {
                            // 微信支付订单，也更新会员等级
                            // 增加用户充值总额
                            $total_recharge = Redis::incrby(User::TOTAL_RECHARGE_KEY . $order->user_id, $message['total_fee']);
                            $level = User::calcLevel($total_recharge);//计算会员等级
                            UserProfile::query()->where('id', $order->user_id)->update(['level' => $level]);
                            //更新场地为不可选
                            $order->items->each(function ($item) {
                                $item->field_profile->update(['amount' => 0]);
                            });

                            //订场成功后，发送短信或者微信模板消息
                            $day = Redis::hget(OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id, 'day');
                            $msg = Redis::hget(OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id, 'msg');
                            $type = Redis::hget(OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id, 'type');
                            $field_info = '日期为' . $day . '，时段' . $msg;

                            $user = User::query()->where('id', $order->user_id)->first();
                            $temp_id = 's3hPD_voLenSUxpnsgh8o68TiAcLbFZEdaRlSQMrKjI';
                            $data = [
                                'first' => '尊敬的' . $user->nickname . '，您的订场已成功。',
                                'keyword1' => '深圳澳莱芙球馆',//场馆名称
                                'keyword2' => '室内' . $type == 1 ? '羽毛球' : '篮球',//消费项目
                                'keyword3' => $field_info,//场地信息
                                'keyword4' => $message['total_fee'],//付款金额
                                'remark' => '澳莱芙球馆感谢您的惠顾！',
                            ];
                            app(Wechat::class)->sendTempMsg($message['openid'], $temp_id, $data);

                            //如果绑定了手机号，发个短信通知下
                            if ($user && $user->mobile_no) {
                                $result = EasySms::send($user->mobile_no, [
                                    'template' => 'SMS_156280001',
                                    'data' => [
                                        'name' => $user->nickname,
                                        'activity' => '室内' . $type == 1 ? '羽毛球' : '篮球',
                                        'date' => $day,
                                        'time' => $field_info
                                    ],
                                ]);
                                Log::info('sms_code_result', $result);
                            }
                            //发完信息，删除redis中的信息
                            Redis::del([OrderService::RESERVE_FIELD_INFO_MSG_KEY . $order->id]);
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