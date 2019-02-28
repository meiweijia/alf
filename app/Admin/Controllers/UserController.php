<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('用户管理')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);

        $grid->id('Id');
        $grid->mobile_no('手机号');
        $grid->nickname('昵称');
        $grid->sex('性别')->display(function ($value) {
            return $value ? ($value == 1 ? '男' : '女') : '未知';
        });
        $grid->column('profile.balance', '余额');
        $grid->created_at('Created at');

        $grid->disableCreateButton();
        $grid->disableRowSelector();

        $grid->filter(function ($filter) {
            $filter->expand();
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->column(6, function ($filter) {
                $filter->like('mobile_no', '手机号');
            });
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            // append一个操作
            $id = $this->getKey();
            $actions->append('<a href="javascript:recharge(' . $id . ')"><i class="fa fa-plus"></i>充值</a>');
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->id('Id');
        $show->mobile_no('手机号');
        $show->openid('Openid');
        $show->nickname('昵称');
        $show->sex('性别')->as(function ($value) {
            return $value ? ($value == 1 ? '男' : '女') : '未知';
        });
        $show->language('语言');
        $show->city('城市');
        $show->province('省份');
        $show->country('国家');
        $show->headimgurl('头像')->image();
        $show->unionid('Unionid');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User);

        $form->password('password', 'Password');
        $form->text('mobile_no', 'Mobile no');
        $form->text('openid', 'Openid');
        $form->text('nickname', 'Nickname');
        $form->switch('sex', 'Sex');
        $form->text('language', 'Language');
        $form->text('city', 'City');
        $form->text('province', 'Province');
        $form->text('country', 'Country');
        $form->text('headimgurl', 'Headimgurl');
        $form->text('unionid', 'Unionid');

        return $form;
    }

    public function recharge(Request $request)
    {
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');

        return UserProfile::query()->where('user_id', $user_id)->increment('balance', $amount * 100);
    }
}
