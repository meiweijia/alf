<?php
/**
 * Created by PhpStorm.
 * User: mei
 * Date: 2019/1/7
 * Time: 9:12
 */

namespace App\Libraries;


use Illuminate\Http\Request;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Facades\Log;

class Wechat
{
    // 公众号
    const WECHAT_TYPE_OFFICIAL_ACCOUNT = 'official_account';
    // 微信支付
    const WECHAT_TYPE_PAYMENT = 'payment';
    // 小程序
    const WECHAT_TYPE_MINI_PROGRAM = 'mini_program';
    // 开放平台
    const WECHAT_TYPE_OPEN = 'open_platform';
    // 企业微信
    const WECHAT_TYPE_WORK = 'work';
    // 企业微信开放平台
    const WECHAT_TYPE_OPEN_WORK = 'open_work';

    public function serve()
    {
        $app = app('wechat.' . self::WECHAT_TYPE_OFFICIAL_ACCOUNT);
        $app->server->push(function ($message) use ($app) {
            // $message['FromUserName'] // 用户的 openid
            // $message['MsgType'] // 消息类型：event, text....
            $user = $app->user->get($message['FromUserName']);
            Log::info('wechat_user',$user);
            switch ($message['MsgType']) {
                case '':
                    switch ($message->Event) {
                        case 'subscribe':
                            return new Text( '欢迎关注 澳莱芙');
                            break;
                    }
            }
        });

        $response = $app->server->serve();

        return $response;
    }

    /**
     * 获取支付信息配置
     *
     * @param string $trade_no
     * @param int $total_fee
     * @param $body
     * @return bool|array
     */
    public function getPaymentConfig($trade_no, $total_fee, $body)
    {
        $app = app('wechat.' . self::WECHAT_TYPE_PAYMENT);
        $result = $app->order->unify([
            'body' => $body,
            'out_trade_no' => $trade_no,
            'total_fee' => $total_fee,
            'trade_type' => 'JSAPI',
            'openid' => self::authUser()->getId(),
            'notify_url' => config('wechat.default.notify_url')
        ]);

        Log::info('下单', $result);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $result = $this->app->jssdk->appConfig($result['prepay_id']);//第二次签名
            Log::info('zhifu', $result);
            $config = $this->app->jssdk->sdkConfig($result['prepayid']);
            return $config;
        } else {
            Log::error('微信支付签名失败:' . var_export($result, 1));
            return false;
        }
    }

    /**
     * 获取微信用户信息
     *
     * @return \Overtrue\Socialite\User
     */
    public static function authUser()
    {
        $app = app('wechat.' . self::WECHAT_TYPE_OFFICIAL_ACCOUNT);
        return $app->oauth->user();
    }

    /**
     * 微信登陆授权
     *
     * @param Request $request
     * @return mixed
     */
    public static function authLogin(Request $request)
    {
        $app = app('wechat.' . self::WECHAT_TYPE_OFFICIAL_ACCOUNT);
        return $app->oauth->scopes(['snsapi_userinfo'])
            ->setRequest($request)
            ->redirect(url($request->input('thisurl')));
    }
}