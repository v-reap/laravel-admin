<?php

namespace Encore\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Models\Task\Attribute;
use Encore\Admin\Models\Task\Status;
use Encore\Admin\Models\Task\Type;
use Encore\Admin\Models\Task\Task;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;

class TaskController extends Controller
{
    use ModelForm;

    public $type;

    public $task;

    public function __construct(Task $task, Type $type)
    {
        $input = Input::all();

        if (isset($input['type'])){
            $this->type=$type->find($input['type']);
        }
        if (isset(\Route::current()->parameters()['task'])){
            $this->task=$task->find(\Route::current()->parameters()['task']);
            $this->type=$this->task->type;
        }
    }

    public function test(Request $request)
    {

        $result = array();
        preg_match_all("/\[([a-z0-9_]+)\]/","value[322][task_value]", $result);
        dd($result[1]);
//        \DB::enableQueryLog();
//        $taskList = Task::find(42325);//with(['status','type','user','value'])
////        dd($this->getAttrs()->toArray());
//        $updateCre=\Encore\Admin\Models\Task\Value::updateOrCreate(['task_id'=>42314,'attribute_id'=>539],['task_value'=>1111111111111]);
//        dd($updateCre->toArray(),$taskList->toArray(),\DB::getQueryLog());
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $typeName=trans('task.Tasks');
            try {
                $typeName=$this->type->name;
            } catch (Exception $e) {}

            $content->header($typeName);
            $content->description('...');

            $content->body($this->grid()->render());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Task::class, function (Grid $grid) {
//            $grid->id('ID')->sortable();
//            $grid->column('text19','SKU');
            if ($this->type && $this->type->id){
                $attributes=Attribute::all()->where('type_id','=',$this->type->id);
                $grid->model()->where('type_id','=',$this->type->id);
                foreach ($attributes as $attribute) {
                    if (!$attribute->not_list){
                        $gData=$grid->column($attribute->frontend_label)->display(function () use ($attribute) {
                            $val = (array_column($this->value->toArray(),'task_value','attribute_id'));
                            $data = isset($val[$attribute->id])?$val[$attribute->id]:'';
                            if ($attribute->frontend_input=='image'){
                                return '<img src="'.$data.'" width=100px />';
                            }elseif ($attribute->frontend_input=='file'){
                                return $data ? '<a href="/uploads/'.$data.'" target="_blank" ><i class="fa fa-download"></i></a>':'';
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
            }

            $grid->column('hours',trans('task.hours'))->sortable();
            if (Admin::user()->can('tasks.price')){
                $grid->column('price',trans('task.price'))->sortable();
            }
            $grid->column('status.name',trans('task.status_id'))->sortable();
            $grid->column('title',trans('task.title'))->limit(30);//->editable('text')
            $grid->column('end_at',trans('task.end_at'))->sortable();//->editable('datetime')
            $grid->column('created_at',trans('task.created_at'))->sortable();
            $grid->column('updated_at',trans('task.updated_at'))->sortable();

            $grid->disableCreateButton();
            if(!Admin::user()->isAdministrator()){
                $grid->actions(function ($actions) {
                    $actions->disableDelete();
                });
            }
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
//            $typeName=trans('task.Tasks');
            $typeName=$this->task->type->name;
            $this->type=$this->task->type;
            $content->header(trans('task.Edit').$typeName);
            $content->description('...');

            $content->body($this->form()->edit($id));
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
//            $typeName=trans('task.Tasks');
            $typeName=$this->type->name;
            $content->header(trans('task.Create').$typeName);
            $content->description('...');
            $content->body($this->form());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //todo eav submit
        return Admin::form(Task::class, function (Form $form) {
//            $form->display('id', 'ID');
            $form->hidden('user_id', trans('task.user_id'))->value(Admin::user()->id);
            $form->hidden('type_id', trans('task.type_id'))->value($this->type->id);
            $form->hidden('type', trans('task.type_id'))->value($this->type->id);
            $form->text('title', trans('task.title'))->attribute('required','required')
                ->placeholder(trans('task.Please Enter...'))->rules('required');
            $form->decimal('time_limit', trans('task.time_limit'));
            if (Admin::user()->can('tasks.price')){
                $form->currency('price', trans('task.price'))->symbol('￥');
            }
            $form->datetime('end_at', trans('task.end_at'))->default(Carbon::now())->rules('required');
            $form->select('status_id', trans('task.status_id'))->options(Status::all()->pluck('name','id'))
                ->rules('required')->attribute('required','required');

            foreach ($this->type->attribute->sortBy('orderby')->toArray() as $attribute) {
                $form->hidden('value['.$attribute['id'].'][attribute_id]')->value($attribute['id']);
                $attField = $form->{$attribute['frontend_input']}(
                    'value['.$attribute['id'].'][task_value]',$attribute['frontend_label']);
                if($attribute['frontend_input'] == 'select') {
                    $option = explode('|',$attribute['option']);
                    $attField = $attField->options(array_combine($option,$option));
                }
                if($this->task){
                    $value=$this->task->value->where('attribute_id','=',$attribute['id'])->first();
                    $attField = $value ? $attField->value($value->task_value) : $attField;
                }
                if($attribute['rules']) {
                    $attField = $attField->attribute('required','required');
                }
            }
//            $form->display('created_at', 'Created At');
//            $form->display('updated_at', 'Updated At');

            $form->builder()->getTools()->disableListButton();
        });
    }
}
