<?php

namespace App\Http\Controllers;

use App\Libraries\Wechat;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function wechat_serve(){
        return app(Wechat::class)->serve();
    }
}
