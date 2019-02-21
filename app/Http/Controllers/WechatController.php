<?php

namespace App\Http\Controllers;

use App\Libraries\Wechat;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function serve()
    {
        $msg = '欢迎来到澳莱芙球馆。' . PHP_EOL .
            '球馆营业时间：' . PHP_EOL .
            '周一至周五：上午 10:00-12:00 下午 14:00-22:00' . PHP_EOL .
            '周六、周日、节假日：上午 09:30-12:30 下午 14:00-22:00' . PHP_EOL .
            '地址：深圳市龙岗区坂田街道南坑村麒麟路富奇智汇园B03' . PHP_EOL .
            '运动热线：秦教练 18680254124';
        return app(Wechat::class)->serve($msg);
    }
}
