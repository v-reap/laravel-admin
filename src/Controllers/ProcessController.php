<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Models\Workflow\Process;
use Illuminate\Routing\Controller;

class ProcessController extends Controller
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
            $content->header(trans('workflow.Process'));
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
            $content->header(trans('workflow.Process'));
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
            $content->header(trans('workflow.Process'));
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
        return Admin::grid(Process::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('workflow_id',trans('workflow.workflow_id'));
            $grid->column('entity_id',trans('workflow.entity_id'));
            $grid->column('user_id',trans('workflow.user_id'));
            $grid->column('title',trans('workflow.title'));
            $grid->column('serialized_workflow',trans('workflow.serialized_workflow'));
            $grid->column('process_data',trans('workflow.process_data'));
            $grid->column('end_date',trans('workflow.end_date'));
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
        return Admin::form(Process::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('workflow_id',trans('workflow.workflow_id'))->rules('required');
            $form->text('entity_id',trans('workflow.entity_id'))->rules('required');
            $form->text('user_id',trans('workflow.user_id'))->rules('required');
            $form->text('title',trans('workflow.title'))->rules('required');
            $form->text('serialized_workflow',trans('workflow.serialized_workflow'))->rules('required');
            $form->text('process_data',trans('workflow.process_data'))->rules('required');
            $form->text('end_date',trans('workflow.end_date'))->rules('required');
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

        });
    }
}
