<?php

namespace App\Admin\Controllers;


use App\Models\User;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ReserveController extends Controller
{
    public function index(Content $content)
    {
        // 选填
        $content->header('填写页面头标题');

        // 选填
        $content->description('填写页面描述小标题');

        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/admin'],
            ['text' => '用户管理', 'url' => '/admin/users'],
            ['text' => '编辑用户']
        );

        // 填充页面body部分，这里可以填入任何可被渲染的对象
        $content->body('hello world');

        // 在body中添加另一段内容
        $content->body('foo bar');

        // `row`是`body`方法的别名
        $content->row('hello world');

        return $content;
    }

    public function badminton(Content $content)
    {
        // 选填
        $content->header('羽毛球预定');


        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/admin'],
            ['text' => '羽毛球预定', ]
        );

        // 填充页面body部分，这里可以填入任何可被渲染的对象
        $content->body('<iframe name="main1" src="http://sports.mjmhu.cn/homeselectOptPage" frameborder="0" width="100%" height="980px"></iframe>');

        return $content;
    }

    public function basketball(Content $content)
    {
        // 选填
        $content->header('篮球预定');


        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/admin'],
            ['text' => '蓝球预定', ]
        );

        // 填充页面body部分，这里可以填入任何可被渲染的对象
        $content->body('<iframe name="main2" src="http://sports.mjmhu.cn/homeselectOptPages" frameborder="0" width="100%" height="980px"></iframe>');

        return $content;
    }
}