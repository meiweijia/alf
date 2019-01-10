<?php

namespace App\Libraries;


use Illuminate\Http\Request;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Facades\Log;
use Overtrue\LaravelWeChat\Facade as EasyWechat;

class Wechat
{
    /**
     * 微信服务启动
     *
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    public function serve($name)
    {
        $app = EasyWechat::officialAccount();
        $app->server->push(function ($message) use ($name) {
            // $message['FromUserName'] // 用户的 openid
            // $message['MsgType'] // 消息类型：event, text....
            switch ($message['MsgType']) {
                case 'event':
                    switch ($message->Event) {
                        case 'subscribe':
                            return new Text('欢迎关注 ' . $name);
                            break;
                    }
            }
        });

        $response = $app->server->serve();

        return $response;
    }

    /**
     * 获取微信配置
     *
     * @return array|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getWechatJssdkConfig($url)
    {
        $app = EasyWechat::officialAccount();
        $app->jssdk->setUrl($url);
        return $app->jssdk->buildConfig([
            "chooseWXPay" //微信支付
        ], true);
    }

    /**
     * 获取支付信息配置
     *
     * @param $trade_no
     * @param $total_fee
     * @param $body
     * @return bool
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getPaymentConfig($trade_no, $total_fee, $body)
    {
        $app = EasyWechat::payment();
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
        $app = EasyWechat::officialAccount();
        return $app->oauth->user();
    }

    /**
     * 根据openid获取用户
     *
     * @param $id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function getUserById($id)
    {
        $app = EasyWechat::officialAccount();
        return $app->user->get($id);
    }

    /**
     * 微信登陆授权
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public static function authLogin(Request $request)
    {
        $app = EasyWechat::officialAccount();
        $num = generate_code();
        $key = 'oauth_return_url_' . $num;
        cache([$key => $request->input('thisurl')], 1);
        return $app->oauth->setRequest($request)->redirect(route('v1.user.check_bind_mobile', $key));
    }

}