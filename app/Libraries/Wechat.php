<?php

namespace App\Libraries;


use App\Models\User;
use Illuminate\Http\Request;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
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
                    switch ($message['Event']) {
                        case 'subscribe':
                            return new Text($name);
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
        $par = [
            'body' => $body,
            'out_trade_no' => $trade_no,
            'total_fee' => $total_fee,
            'trade_type' => 'JSAPI',
            'openid' => User::query()->where('id', Auth::id())->pluck('openid')->first(),
            'notify_url' => config('wechat.payment.default.notify_url')
        ];

        $result = $app->order->unify($par);

        Log::info('下单', $result);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $result = $app->jssdk->appConfig($result['prepay_id']);//第二次签名
            $config = $app->jssdk->sdkConfig($result['prepayid']);
            return $config;
        } else {
            return false;
        }
    }

    /**
     * 获取微信用户信息
     *
     * @param Request $request
     * @return \Overtrue\Socialite\Providers\WeChatProvider|\Overtrue\Socialite\User
     */
    public static function authUser(Request $request)
    {
        $app = EasyWechat::officialAccount();
        return $app->oauth->setRequest($request)->user();
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
        return $app->oauth->setRequest($request)->redirect();
    }

    /**
     * 发送模板消息
     * @param string $open_id
     * @param string $temp_id
     * @param string $url
     * @param array $data
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @return array
     */
    public function sendTempMsg(string $open_id, string $temp_id, array $data, string $url = null)
    {
        $app = EasyWechat::officialAccount();
        $send = [
            'touser' => $open_id,
            'template_id' => $temp_id,
            'data' => $data
        ];
        if ($url) {
            $send['url'] = $url;
        }
        return $app->template_message->send($send);
    }

}