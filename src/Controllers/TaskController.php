<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Models\Task\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ModelForm;

    public function test(Request $request)
    {
        $taskList = Task::with(['status','type','user','value'])->first();
        $data = ["tasktype_id" => "2",
            "title" => "主题",
            "taskstatus_id" => "1",
            "end_at" => "2018-04-10 12:10:28",
            "hours" => "0.5",
            "attribute" => [
                534 => "SKU",
                535 => "品牌",
                536 => "产品",
                538 => "自营",
                539 => "样品",
                540 => "2018-04-10",
            ],
            "content" => "23213<audio controls=\"controls\" style=\"display: none;\"></audio>",
        ];
        \DB::enableQueryLog();
        dd($taskList->toArray(),\DB::getQueryLog());
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index($id=0)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('header');
            $content->description('description');

            $content->body($this->grid($id)->render());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($id)
    {
        return Admin::grid(Task::class, function (Grid $grid) use ($id) {

            $grid->id('ID')->sortable();
            $attributes=\Encore\Admin\Models\Task\Attribute::all()->where('type_id','=',$id);
            $grid->model()->where('type_id','=',$id);
            foreach ($attributes as $attribute) {
//                dd($attribute->toArray());
                if (!$attribute->not_list){
                    $grid->column($attribute->frontend_label)->display(function () use ($attribute) {
                        $val = (array_column($this->value->toArray(),'task_value','attribute_id'));
                        $data = isset($val[$attribute->id])?$val[$attribute->id]:'';
                        if ($attribute->frontend_input=='image'){
                            return '<img src="'.$data.'" width=100px />';
                        }else{
                            return $data;
                        }
                    });//->editable($attribute->frontend_input)
                }
            }

            $grid->column('type.name',trans('task.type_id'));
            $grid->column('status.name',trans('task.status_id'));
            $grid->column('title',trans('task.title'));//->editable('text')
            $grid->column('end_at',trans('task.end_at'));//->editable('datetime')
            $grid->column('created_at',trans('created_at'));
            $grid->column('updated_at',trans('updated_at'));

//            $grid->content(trans('task.content'));
//            $grid->hours(trans('task.hours'));
//            $grid->price(trans('task.price'));
//            $grid->task_id(trans('task.task_id'));
//            $grid->user_id(trans('task.user_id'));
//            $grid->type_id(trans('task.type_id'));

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Task::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
