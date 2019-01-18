<?php

namespace App\Http\Controllers;

use App\Libraries\Wechat;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function serve()
    {
        $msg = '澳莱芙球馆！'.PHP_EOL.'温馨提示：绑定手机号可以接收订场确认短信；充值会员也可以通过手机号找回。';
        return app(Wechat::class)->serve($msg);
    }
}
