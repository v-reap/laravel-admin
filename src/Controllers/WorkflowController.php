<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Models\Task\Type;
use Encore\Admin\Models\Workflow\Workflow;
use Illuminate\Routing\Controller;

class WorkflowController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('workflow.Workflow'));
            $content->description(trans('admin.list'));
            $content->body($this->grid()->render());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('workflow.Workflow'));
            $content->description(trans('admin.edit'));
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
            $content->header(trans('workflow.Workflow'));
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Workflow::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('type_id',trans('workflow.type_id'));
            $grid->column('name',trans('workflow.name'));
            $grid->column('bpmn',trans('workflow.bpmn'));
            $grid->column('version',trans('workflow.version'));
            $grid->column('user_id',trans('workflow.user_id'));
            $grid->created_at(trans('admin.created_at'));
            $grid->updated_at(trans('admin.updated_at'));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(Workflow::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->select('type.name',trans('workflow.type_id'))
                ->options(Type::all()->pluck('name','id'))->rules('required');
            $form->text('name',trans('workflow.name'))->rules('required');
            $form->mobile('version',trans('workflow.version'))->options(['mask' => '9.99.99'])->rules('required');
            $form->hidden('user_id', trans('task.user_id'))->value(Admin::user()->id);
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));
            $form->bpmn('bpmn',trans('workflow.bpmn'))->rules('required');

        });
    }
}
