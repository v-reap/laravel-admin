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

    public $lastTasks = [];

    public function __construct(Task $task, Type $type)
    {
        $input = Input::all();

        if (isset($input['type'])){
            $this->type=$type->find($input['type']);
        }
        if (isset(\Route::current()->parameters()['task'])){
            $this->task=$task->find(\Route::current()->parameters()['task']);
            $this->type=$this->task ? $this->task->type : null;
            $this->displayLastTask($this->task);
        }
    }

    public function test(Request $request)
    {
        $task = Task::find(42337);

//        $newTask = Task::create(["title" => '2222222222222222',
//            "user_id" => $task->user_id,
//            "status_id" => 1,
//            "type_id" => $task->type->next_id,
//            "root_id" => $task->id,
//            "last_id" => $task->id,
//        ]);
        dd($task->next->root->toArray());
//        \DB::enableQueryLog();
//        $taskList = Task::find(42325);//with(['status','type','user','value'])
////        dd($this->getAttrs()->toArray());
//        $updateCre=\Encore\Admin\Models\Task\Value::updateOrCreate(['task_id'=>42314,'attribute_id'=>539],['task_value'=>1111111111111]);
//        dd($updateCre->toArray(),$taskList->toArray(),\DB::getQueryLog());
    }

    public function workflow($id, Request $request)
    {
        $input = $request->all();
//        \Log::debug($input);
        $title = isset($input['title']) ? $input['title'] : $id;
        if(!isset($input['assignableUser'])){
            return response()->json([
                'status'  => false,
                'message' => trans('task.Action').trans('task.Error').'! '.trans('task.No assignable User Selected!'),
            ]);
        }
        $user_id = $input['assignableUser'];
        $complateTasks = [];
        $ids = explode(',', $id);
        $tasks = Task::find($ids);
        foreach ($tasks as $task) {
            if ($task->next && $task->next->status_id==5){
                $complateTasks[]=$task->next->id;
                continue;
            }
            \DB::beginTransaction();
            try {
                $newTask = Task::updateOrCreate(
                    ['id'=>$task->next_id],
                    ["title" => $title,
                    "user_id" => $user_id,
                    "status_id" => 1,
                    "type_id" => $input['type'],//$task->type->next_id
                    "root_id" => $task->root_id ? $task->root_id : $task->id,
                    "last_id" => $task->id,
                    ]);
                $task->next_id=$newTask->id;
                $task->save();
            } catch (Exception $e) {
                \DB::rollback();
                \Log::error($e);
                return response()->json([
                    'status'  => false,
                    'message' => trans('task.Action').trans('task.Error').'! ',
                ]);
            }
            \DB::commit();
        }

        if ($complateTasks){
            return response()->json([
                'status'  => false,
                'message' => trans('task.The following tasks have submited with Complated status which have been ignore:')
                    .implode(', ',$complateTasks).'. ',
            ]);
        }else{
            return response()->json([
                'status'  => true,
                'message' => trans('task.Action').trans('task.Success').'! ',
            ]);
        }
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
            $grid->id('ID')->sortable();
            if ($this->type && $this->type->id){
                $attributes=Attribute::all()->where('type_id','=',$this->type->id);
                $grid->model()->where('type_id','=',$this->type->id);
                foreach ($attributes as $attribute) {
                    if (!$attribute->not_list){
                        $thisController = $this;
                        $gData=$grid->column($attribute->frontend_label)
                            ->display(function () use ($attribute,$thisController) {
                            $val = (array_column($this->value->toArray(),'task_value','attribute_id'));
                            $data = isset($val[$attribute->id])?$val[$attribute->id]:'';
                            return $thisController->displayAttr($attribute->frontend_input,$data);
                        });//->editable($attribute->frontend_input)
                        if ($attribute->frontend_input=='text'){
                            $gData->limit(30);
                        }
                    }
                }
            }else{
                $grid->column('type.name',trans('task.type_id'));
            }

            $grid->column('time_limit',trans('task.time_limit'))->sortable();
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
                $grid->tools(function ($tools) {
                    $tools->batch(function ($batch) {
                        $batch->disableDelete();
                    });
                });
            }
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->add($this->type->next->name, new Grid\Tools\BatchWorkflow($this->type->id));
                });
            });

//            $assignableUser = Admin::user()->roles->firstWhere('leader_id','=',Admin::user()->leader_id);
            $grid->setActionAttrs($this->type->next->name,Admin::user()->assignableUser(),$this->type->next->assigned_to);
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
            $form = $form->tab($this->type->name, function ($form) {

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

                $this->eavForm($form,$this->task,$this->type);
            });

            foreach ($this->lastTasks as $lastTask) {
                $form->tab($lastTask->type->name, function ($form) use ($lastTask) {
                    $this->eavForm($form,$lastTask,$lastTask->type,true);
                });
            }
//            $form->display('created_at', 'Created At');
//            $form->display('updated_at', 'Updated At');

            $form->builder()->getTools()->disableListButton();
        });
    }

    public function eavForm($form,$task,$type,$readOnly=false)
    {
        foreach ($type->attribute->sortBy('orderby')->toArray() as $attribute) {
            if (!$readOnly){
                $form->hidden('value['.$attribute['id'].'][attribute_id]')->value($attribute['id']);
                $attField = $form->{$attribute['frontend_input']}(
                    'value['.$attribute['id'].'][task_value]',$attribute['frontend_label']);
                if($attribute['frontend_input'] == 'select') {
                    $option = explode('|',$attribute['option']);
                    $attField = $attField->options(array_combine($option,$option));
                }
                if($task){
                    $value=$task->value->where('attribute_id','=',$attribute['id'])->first();
                    $attField = $value ? $attField->value($value->task_value) : $attField;
                }
                if($attribute['rules']) {
                    $attField = $attField->attribute('required','required');
                }
            }else{
                $value=$task->value->where('attribute_id','=',$attribute['id'])->first();
                if ($value){
                    $value = $this->displayAttr($attribute['frontend_input'],$value->task_value,false);
                    $form->display('value'.$attribute['id'],$attribute['frontend_label'])->with(function () use ($value) {
                        return $value;
                    });
                }
            }
        }
    }

    public function displayAttr($type,$data,$isList=true)
    {
        if ($type=='image'){
            $data=substr($data,0,6)=='images' ? '/uploads/'.$data : $data;
            return '<img src="'.$data.'" '.($isList?'width="100px"':'').' />';
        }elseif ($type=='file'){
            return $data ? '<a href="/uploads/'.$data.'" target="_blank" ><i class="fa fa-download"></i>'.
                ($isList?'':$data).'</a>':'';
        }else{
            return $data;
        }
    }

    public function displayLastTask($task)
    {
        if ($task->root_id){
            $this->lastTasks[] = $task->last;
            return $this->displayLastTask($task->last);
        }
    }
}
