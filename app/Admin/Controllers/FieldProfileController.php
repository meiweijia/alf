<?php

namespace App\Admin\Controllers;

use App\Models\Field;
use App\Models\FieldProfile;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class FieldProfileController extends Controller
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
        $field_id = Input::get('id');

        $header = '价格维护';
        $description = '价格列表';

        if ($field_id) {
            $field = Field::query()->find($field_id);
            $header = Field::$typeMap[$field->type] . '-' . $field->name;
        }

        return $content
            ->header($header)
            ->description($description)
            ->body($this->grid($field_id));
    }

    public function getList(Content $content, $id)
    {
        $name = '场地一';
        return $content
            ->header($name)
            ->description('价格设置')
            ->body($this->grid($id));
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
    protected function grid($id = null)
    {
        $grid = new Grid(new FieldProfile);

        if ($id)
            $grid->model()->where('field_id', $id);

        $grid->id('Id');
        $grid->weekday('星期')->display(function ($value) {
            return week_map($value);
        });;
        $grid->time('时间')->display(function ($value) {
            return time_map($value);
        });
        $grid->fees('价格')->editable();
        $grid->amount('能否预定')->display(function ($value) {
            return $value ? '是' : '否';
        });

        $grid->filter(function ($filter) {
            $filter->expand();
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            // 在这里添加字段过滤器
            $filter->column(6, function ($filter) {
                $filter->equal('weekday', '星期')->select(week_arr());
            });
            $filter->column(6, function ($filter) {
                $filter->equal('time', '时间')->select(time_arr());
            });

        });

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
        $show = new Show(FieldProfile::findOrFail($id));

        $show->id('Id');
        $show->field_id('Field id');
        $show->weekday('Weekday');
        $show->time('Time');
        $show->fees('Fees');
        $show->amount('Amount');
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
        $form = new Form(new FieldProfile);

        $form->number('field_id', 'Field id');
        $form->switch('weekday', 'Weekday');
        $form->switch('time', 'Time')->default(1);
        $form->decimal('fees', 'Fees');
        $form->switch('amount', 'Amount')->default(1);

        return $form;
    }
}
