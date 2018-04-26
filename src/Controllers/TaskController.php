<?php

namespace Encore\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\MessageBag;
use Exception;

class TaskController extends Controller
{
    use ModelForm;

    public $type = null;

    public $task = null;

    public $isComplete = null;

    public $lastTasks = [];

    public function __construct(Task $task, Type $type)
    {
        $input = Input::all();

        if (isset($input['type'])){
            $this->type=$type->find($input['type']);
        }
        if (isset($input['complete'])){
            $this->isComplete=$input['complete'];
        }
        if (isset(\Route::current()->parameters()['task'])){
            $this->task=$task->find(\Route::current()->parameters()['task']);
            $this->type=$this->task ? $this->task->type : null;
            $this->displayLastTask($this->task);
        }
    }

    public function test(Request $request)
    {
        $rule1 = [
            'saving'=>[
                'if_key'=>'status_id',
                'if_con'=>'==',
                'if_value'=>5,
                'error'=>[
                    'title'   => '提交失败',
                    'message' => '已完成任务无法修改，请联系系统管理员！',
                ]
            ]
        ];
        $rule2 = [
            'saved'=>[
                'if'=>[
                    [
                        'key'=>'status_id',
                        'con'=>'==',
                        'value'=>5,
                    ],
                    [
                        'key'=>'type.next',
                        'con'=>'>',
                        'value'=>0,
                    ],
                ],
                'action' => [
                    'saveAssign'=>['user_id','系统自动分配:'],
                ],
                'success'=>[
                    'title'   => '提交成功',
                    'message' => '系统将自动分配到下一个任务环节！',
                ]
            ]
        ];
//        $task = Task::find(42517);
//        dd($task->toArray());
//        \DB::enableQueryLog();
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
        $errorTasks = [];
        $ids = explode(',', $id);
        $tasks = Task::find($ids);
        foreach ($tasks as $task) {
            if ($task->next && $task->next->status_id==5){
                $complateTasks[] = $task->id;
            }

            if (!$task->saveAssign($user_id,$title)){
                $errorTasks[] = $task->id;
            }
        }

        if ($complateTasks || $errorTasks){
            $message = $errorTasks ? trans('task.Action').trans('task.Error').'('.implode(', ',$complateTasks).')! ' : '';
            $message .= $complateTasks ? trans('task.The following tasks have submited with Complated status which have been ignore:').implode(', ',$complateTasks).'. ' : '';
            return response()->json([
                'status'  => false,
                'message' => $message,
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
            $userIds = Administrator::where('leader_id',Admin::user()->id)->get()->pluck('id')->toArray();
            $userIds[] = Admin::user()->id;
            if ($this->isComplete==5){
                $grid->model()->where('status_id','=',5);
            } else {
                $grid->model()->where('status_id','<>',5);
            }
            $grid->model()->whereIn('user_id',$userIds);
            $this->getColumns($grid);
            $this->getActions($grid);
            $this->getTools($grid);
            $this->getFilter($grid);
        });
    }

    public function getColumns($grid)
    {
        $grid->id('ID')->sortable();
        $grid->column('status.name',trans('task.status_id'));//->sortable();
        $grid->column('title',trans('task.title'))->limit(30);//->editable('text')
        $grid->column('end_at',trans('task.end_at'))->sortable();//->editable('datetime')
        if ($this->type && $this->type->id){
            $this->getColumnEAV($grid);
        }else{
            $grid->column('type.name',trans('task.type_id'));
        }
        $grid->column('time_limit',trans('task.time_limit'))->sortable();
        if (Admin::user()->can('tasks.price')){
            $grid->column('price',trans('task.price'))->sortable();
        }
        $grid->column('created_at',trans('task.created_at'))->sortable();
        $grid->column('updated_at',trans('task.updated_at'))->sortable();
    }

    public function getColumnEAV($grid)
    {
        $attributes=Attribute::where('type_id','=',$this->type->id)
            ->orWhere('type_id','=',$this->type->root_id)->get();
        $grid->model()->where('type_id','=',$this->type->id);
        foreach ($attributes as $attribute) {
            if (!$attribute->not_list){
                $thisController = $this;
                $gData=$grid->column($attribute->frontend_label)
                    ->display(function () use ($attribute,$thisController) {
                        $val = (array_column(array_merge(
                            $this->value?$this->value->toArray():[],
                            $this->rootValue?$this->rootValue->toArray():[]),
                            'task_value','attribute_id'));
                        $data = isset($val[$attribute->id])?$val[$attribute->id]:'';
                        return $thisController->displayAttr($attribute->frontend_input,$data);
                    });//->editable($attribute->frontend_input)
                if ($attribute->frontend_input=='text'){
                    $gData->limit(30);
                }
            }
        }
    }

    public function getActions($grid)
    {
        $grid->disableCreateButton();
        if(!Admin::user()->isAdministrator()){
            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });
        }
    }

    public function getTools($grid)
    {
        if(!Admin::user()->isAdministrator()){
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        }
        if ($this->type && $this->type->next){
            $grid->setActionAttrs($this->type->next->name,Admin::user()->assignableUser(),$this->type->assigned_to);
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->add($this->type->next->name, new Grid\Tools\BatchWorkflow($this->type->next->id));
                });
            });
        }
    }

    public function getFilter($grid)
    {
        $grid->filter(function ($filter)  {
            if (!$this->type){
                $filter->equal('type_id',trans('task.type_id'))->select(Type::all()->pluck('name','id'));
            }
            $filter->equal('status_id',trans('task.status_id'))->select(Status::all()->pluck('name','id'));
            $filter->equal('user_id',trans('task.user_id'))->select(Admin::user()->assignableUser());
            $filter->like('title',trans('task.title'));
            $filter->between('end_at',trans('task.end_at'))->datetime();
            $filter->between('created_at',trans('task.created_at'))->datetime();
            $filter->between('updated_at',trans('task.updated_at'))->datetime();
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
        return Admin::form(Task::class, function (Form $form) {
//            $form->display('id', 'ID');
            $this->getFieldForm($form);
            $form->builder()->getTools()->disableListButton();
            $this->task ? $this->getOnSaveForm($form) : null;
        });
    }

    public function getFieldForm($form)
    {
        $form = $form->tab($this->type->name, function ($form) {
            if(!$this->task){
                $form->hidden('user_id', trans('task.user_id'))->value(Admin::user()->id);
            }
            $form->hidden('type_id', trans('task.type_id'))->value($this->type->id);
            $form->hidden('type', trans('task.type_id'))->value($this->type->id);
            $form->text('title', trans('task.title'))->attribute('required','required')
                ->placeholder(trans('task.Please Enter...'))->rules('required');
            $form->decimal('time_limit', trans('task.time_limit'));
            if (Admin::user()->can('tasks.price')){
                $form->currency('price', trans('task.price'))->symbol('￥');
            }
            $form->datetime('end_at', trans('task.end_at'))->default(Carbon::now())->rules('required');
            $isReadOnly = false;//$this->task ? !(Admin::user()->id==$this->task->user_id || Admin::user()->isAdministrator()):false;
            $this->getEAVFieldForm($form,$this->task,$this->type,$isReadOnly);
            $this->getStatusField($form);
        });
        foreach ($this->lastTasks as $lastTask) {
            $form->tab($lastTask->type->name, function ($form) use ($lastTask) {
                $this->getEAVFieldForm($form,$lastTask,$lastTask->type,true);
            });
        }
    }

    public function getStatusField($form)
    {
        if($this->task){// && $this->type->is_approvable
            $form->divide();
            $form->display('status_id', trans('task.status_id'))->with(function ($value) {
                $status = Status::find($value);
                return $status ? $status->name : '';
            });
            $statusLabel = trans('task.Review');
            if ($this->task && Admin::user()->id==$this->task->user_id && !$this->type->next_id){
                $statusLabel = trans('task.Submit');
                $states = ['on' => ['value' => 2, 'text' => trans('task.Processing'), 'color' => 'warning'],
                    'off'  => ['value' => 5, 'text' => trans('task.Complete'), 'color' => 'success'],];
            }elseif ($this->task && Admin::user()->id==$this->task->user_id && $this->type->is_approvable){
                $statusLabel = trans('task.Submit').trans('task.leader').trans('task.Review');
                $states = ['on'  => ['value' => 8, 'text' => trans('task.Review'), 'color' => 'success'],
                    'off' => ['value' => 2, 'text' => trans('task.TempSave'), 'color' => 'warning'],];
            }elseif ($this->task && Admin::user()->id==$this->task->user_id && !$this->type->is_approvable){
                $statusLabel = trans('task.Submit');
                $states = ['on' => ['value' => 4, 'text' => trans('task.Cancel'), 'color' => 'danger'],
                    'off'  => ['value' => 2, 'text' => trans('task.Processing'), 'color' => 'success'],];
            }else{
                $states = ['on'  => ['value' => 7, 'text' => trans('task.Disapprove'), 'color' => 'danger'],
                    'off' => ['value' => 6, 'text' => trans('task.Approve'), 'color' => 'success'],];
            }
            $form->switch('status_id', $statusLabel)->states($states)->value($states['off']['value']);
        } else {
//            $form->select('status_id', trans('task.status_id'))->options(Status::all()->pluck('name','id'))->rules('required')->attribute('required','required');
            $form->hidden('status_id', trans('task.status_id'))->value(1);
        }
    }

    public function getOnSaveForm($form)
    {
        $form->saving(function ($form) {
            if ($form->model()->status_id==5){
                $error = new MessageBag([
                    'title'   => '提交失败',
                    'message' => '已完成任务无法修改，请联系系统管理员！',
                ]);
                return back()->with(compact('error'));
            }
        });
        $form->saved(function ($form) {
            $message = '';
            if ($form->model()->status_id==6){
                $message .= $form->model()->title.'当前状态为'.$form->model()->status->name;
                if ($form->model()->type->next){
                    $form->model()->saveAssign($form->model()->type->assigned_to ? $form->model()->type->assigned_to : $form->model()->user_id,'提交请求');
                    $message .= '! 系统将自动分配到下一个任务环节（'.$form->model()->type->next->name.'）！';
                }
            }
            if (!$form->model()->type->next_id && $form->model()->status_id==5){
                $lastTasks = $form->model()->saveComplete($this->task);
                $message .= '当前任务流已最终完成，相关子任务已锁定为完成状态，不可修改!';
            }
            $success = new MessageBag([
                'title'   => '任务'.$form->model()->status->name.'！',
                'message' => $message,
            ]);
            if ($message){
                return back()->with(compact('success'));//redirect('/admin/users')
            }
        });
    }

    public function getEAVFieldForm($form,$task,$type,$readOnly=false)
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
                $displayValue = $form->display('value'.$attribute['id'],$attribute['frontend_label']);
                if ($value){
                    $value = $this->displayAttr($attribute['frontend_input'],$value->task_value,false);
                    $displayValue->with(function () use ($value) {
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
            return '<img alt="'.$data.'" src="'.$data.'" '.($isList?'width="100px"':'').' />';
        }elseif ($type=='file'){
            return $data ? '<a alt="'.$data.'" href="/uploads/'.$data.'" target="_blank" ><i class="fa fa-download"></i>'.($isList?'':$data).'</a>':'';
        }elseif ($type=='multipleFile' || $type=='multipleImage'){
            $data = json_decode($data);
            $html = '';
            if (is_array($data)) {
                foreach ($data as $item) {
                    $html .= $item ? '<a alt="'.$item.'" href="/uploads/'.$item.'" target="_blank" ><i class="fa fa-download"></i>'.($isList?'':$item).'</a>'.($isList?' ':'<br/>'):'';
                }
            }
            return $html;
        }else{
            return $data;
        }
    }

    public function displayLastTask($task)
    {
        if ($task && $task->root_id && $task->last){
            $this->lastTasks[] = $task->last;
            return $this->displayLastTask($task->last);
        }
    }
}
