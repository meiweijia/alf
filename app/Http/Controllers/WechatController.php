<?php

namespace App\Http\Controllers;

use App\Libraries\Wechat;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function serve()
    {
        return app(Wechat::class)->serve('澳莱芙');
    }
}
