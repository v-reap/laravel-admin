<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Models\Task\Attribute;
use Encore\Admin\Models\Task\Status;
use Encore\Admin\Models\Task\Type;
use Encore\Admin\Models\Task\Task;
use Illuminate\Routing\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Exception;

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
    public function index(Request $request)
    {
        $typeId = (int)$request->input('type');
        return Admin::content(function (Content $content) use ($typeId) {
            $typeName=trans('task.Tasks');
            try {
                $type=Type::all()->find($typeId);
                $typeName=$type->name;
            } catch (Exception $e) {}

            $content->header($typeName);
            $content->description('...');

            $content->body($this->grid($typeId)->render());
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
//            $grid->id('ID')->sortable();
            if ($id){
                $attributes=Attribute::all()->where('type_id','=',$id);
                $grid->model()->where('type_id','=',$id);
                foreach ($attributes as $attribute) {
                    if (!$attribute->not_list){
                        $gData=$grid->column($attribute->frontend_label)->display(function () use ($attribute) {
                            $val = (array_column($this->value->toArray(),'task_value','attribute_id'));
                            $data = isset($val[$attribute->id])?$val[$attribute->id]:'';
                            if ($attribute->frontend_input=='image'){
                                return '<img src="'.$data.'" width=100px />';
                            }else{
                                return $data;
                            }
                        });//->editable($attribute->frontend_input)
                        if ($attribute->frontend_input=='text'){
                            $gData->limit(30);
                        }
                    }
                }
            }else{
                $grid->column('type.name',trans('task.type_id'));
                $grid->column('hours',trans('task.hours'));
                $grid->column('price',trans('task.price'));
            }

            $grid->column('status.name',trans('task.status_id'));
            $grid->column('title',trans('task.title'))->limit(30);//->editable('text')
            $grid->column('end_at',trans('task.end_at'));//->editable('datetime')
            $grid->column('created_at',trans('created_at'));
            $grid->column('updated_at',trans('updated_at'));

            $grid->disableCreateButton();
//            $grid->content(trans('task.content'));
//            $grid->task_id(trans('task.task_id'));
//            $grid->user_id(trans('task.user_id'));
//            $grid->type_id(trans('task.type_id'));

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
            $typeName=trans('task.Tasks');
            $typeId=null;
            try {
                $task=Task::all()->find($id);
                $typeName=$task->type->name;
                $typeId=$task->type_id;
            } catch (Exception $e) {}

            $content->header(trans('task.Edit').$typeName);
            $content->description('...');

            $content->body($this->form($typeId)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request)
    {
        $typeId = (int)$request->input('type');
        return Admin::content(function (Content $content) use ($typeId) {
            $typeName=trans('task.Tasks');
            try {
                $type=Type::all()->find($typeId);
                $typeName=$type->name;
                $typeId=$type->id;
            } catch (Exception $e) {}

            $content->header(trans('task.Create').$typeName);
            $content->description('...');

            $content->body($this->form($typeId));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($typeId)
    {
        return Admin::form(Task::class, function (Form $form) use ($typeId) {
//            $form->display('id', 'ID');
            $form->hidden('user_id', trans('task.user_id'))->value(1);
            $form->hidden('type_id', trans('task.type_id'))->value($typeId);
            $form->text('title', trans('task.title'))->placeholder(trans('task.Please Enter...'));
            $form->decimal('time_limit', trans('task.time_limit'));
            $form->currency('price', trans('task.price'));
            $form->datetime('end_at', trans('task.end_at'));
            $statusOptions = array_column(Status::all()->toArray(),'name','id');
            $form->select('status_id', trans('task.status_id'))->options($statusOptions);
            $attributes=Attribute::all()->where('type_id','=',$typeId);
//            dd($typeId,$attributes->toArray());
            foreach ($attributes->toArray() as $attribute) {
//                dd($attribute);
                $input=$attribute['frontend_input'];
                $form->$input('value['.$attribute['id'].']',$attribute['frontend_label']);
            }
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->builder()->getTools()->disableListButton();
        });
    }
}
