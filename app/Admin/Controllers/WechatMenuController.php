<?php

namespace App\Admin\Controllers;

use App\Libraries\Wechat;
use App\Models\WechatMenu;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;

class WechatMenuController extends Controller
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
        return Admin::content(function (Content $content) {
            $content->header('树状模型');
            $content->row(function (Row $row) {
                $treeView = WechatMenu::tree();
                $treeView->disableCreate();
                $row->column(6, $this->treeView()->render());

                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
//                    $form->action(admin_base_path('setting/wechat_menus'));

                    $form->select('parent_id', trans('admin.parent_id'))->options(WechatMenu::selectOptions());
                    $form->text('name', '名称');
                    $form->select('type', '类型')->options(['点击', '视图', '菜单']);
                    $form->text('value', '值');

                    $column->append((new Box('新增', $form))->style('success'));
                });
            });
        });
    }

    /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        return WechatMenu::tree(function (Tree $tree) {
            $tree->disableCreate();

            $tree->branch(function ($branch) {
                $payload = "<strong>{$branch['name']}</strong>";

                return $payload;
            });
        });
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
        $grid = new Grid(new WechatMenu);

        $grid->id('Id');
        $grid->parent_id('Parent id');
        $grid->order('Order');
        $grid->name('Name');
        $grid->type('Type');
        $grid->value('Value');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

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
        $show = new Show(WechatMenu::findOrFail($id));

        $show->id('Id');
        $show->parent_id('Parent id');
        $show->order('Order');
        $show->name('Name');
        $show->type('Type');
        $show->value('Value');
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
        $form = new Form(new WechatMenu);

        $form->select('parent_id', trans('admin.parent_id'))->options(WechatMenu::selectOptions());

        $form->text('name', 'Name');
        $form->select('type', '类型')->options(['点击', '视图', '菜单']);
        $form->text('value', 'Value');

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        //保存后回调
        $form->saved(function (Form $form) {
            return (app(Wechat::class)->createMenu());//更新微信菜单
        });

        return $form;
    }
}
