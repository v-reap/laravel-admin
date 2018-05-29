<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Models\Workflow\ProcessItem;
use Illuminate\Routing\Controller;

class ProcessItemController extends Controller
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
            $content->header(trans('workflow.Process Item'));
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
            $content->header(trans('workflow.Process Item'));
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
            $content->header(trans('workflow.Process Item'));
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
        return Admin::grid(ProcessItem::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('process_id',trans('workflow.process_id'));
            $grid->column('entity_id',trans('workflow.entity_id'));
            $grid->column('user_id',trans('workflow.user_id'));
            $grid->column('title',trans('workflow.title'));
            $grid->column('from_state',trans('workflow.from_state'));
            $grid->column('to_state',trans('workflow.to_state'));
            $grid->column('state_id',trans('workflow.state_id'));
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
        return Admin::form(ProcessItem::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('process_id',trans('workflow.process_id'))->rules('required');
            $form->text('entity_id',trans('workflow.entity_id'))->rules('required');
            $form->text('user_id',trans('workflow.user_id'))->rules('required');
            $form->text('title',trans('workflow.title'))->rules('required');
            $form->text('from_state',trans('workflow.from_state'))->rules('required');
            $form->text('to_state',trans('workflow.to_state'))->rules('required');
            $form->text('state_id',trans('workflow.state_id'))->rules('required');
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

        });
    }
}
