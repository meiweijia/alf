<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class OrderController extends Controller
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
            ->header('订单管理')
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
        $grid = new Grid(new Order);

        $grid->model()->whereIn('status', [
            Order::STATUS_APPLIED,
            Order::STATUS_SUCCESS
        ])->orderByDesc('created_at');

        $grid->id('Id');
        $grid->no('订单号');
        $grid->user_id('用户')->display(function ($value) {
            return User::query()->where('id', $value)->pluck('nickname')->first();
        });
        $grid->total_fees('费用');
        $grid->remark('备注');
        $grid->paid_at('支付时间');
        $grid->payment_method('支付方式')->display(function ($value) {
            return $value ? Order::$paymentMap[$value] : null;
        });
        $grid->payment_no('支付方订单');
        $grid->status('状态')->display(function ($value) {
            return Order::$orderStatusMap[$value];
        });
        $grid->type('类型')->display(function ($value) {
            return Order::$typeMap[$value];
        });
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableRowSelector();

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
        $show = new Show(Order::findOrFail($id));

        $show->id('Id');
        $show->no('编号');
        $show->user_id('用户');
        $show->total_fees('费用');
        $show->remark('备注');
        $show->paid_at('支付时间');
        $show->payment_method('支付方式');
        $show->payment_no('支付方订单');
        $show->status('状态');
        $show->type('类型');
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
        $form = new Form(new Order);

        $form->text('no', 'No');
        $form->number('user_id', 'User id');
        $form->decimal('total_fees', 'Total fees');
        $form->textarea('remark', 'Remark');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', 'Payment method');
        $form->text('payment_no', 'Payment no');
        $form->switch('status', 'Status')->default(1);
        $form->switch('type', 'Type')->default(1);

        return $form;
    }
}
