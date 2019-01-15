<?php

namespace App\Admin\Controllers;

use App\Models\Field;
use App\Http\Controllers\Controller;
use App\Models\FieldProfile;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class FieldController extends Controller
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
            ->header('场馆设置')
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
        $grid = new Grid(new Field);

        $grid->id('Id');
        $grid->no('编号');
        $grid->name('场地名称');
        $grid->type('类型')->display(function ($value) {
            return $value == 1 ? '羽毛球' : '篮球';
        });
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');


        $grid->filter(function ($filter) {
            $filter->expand();
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->column(6, function ($filter) {
                $filter->equal('type', '场馆类型')->select([
                    1 => '羽毛球',
                    2 => '篮球',
                ]);
            });

        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            $fee_url = route('field_profiles.index', ['id' => $actions->getKey()]);
            $actions->append("<a href=$fee_url>价格设置</a>");

        });

        $grid->disableCreateButton();
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
        $show = new Show(Field::findOrFail($id));

        $show->id('Id');
        $show->no('No');
        $show->name('Name');
        $show->type('Type');
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
        $form = new Form(new Field);

        $form->number('no', '编号');
        $form->text('name', '场地名称');
        $form->radio('type', '场地类型')->options([
                Field::FIELD_TYPE_SHUTTLECOCK => '羽毛球',
                Field::FIELD_TYPE_BASKETBALL => '篮球']
        )->default(Field::FIELD_TYPE_SHUTTLECOCK);

        return $form;
    }
}
