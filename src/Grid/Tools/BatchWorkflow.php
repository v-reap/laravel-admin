<?php

namespace Encore\Admin\Grid\Tools;

class BatchWorkflow extends BatchAction
{
    protected $action;

    public function __construct($action = 1)
    {
        $this->action = $action;
    }

    /**
     * Script of batch delete action.
     */
    public function script()
    {
        $title = trans('task.Batch').trans('task.Action');
        $confirm = trans('task.Confirmed');
        $cancel = trans('task.Cancel');
        return <<<EOT
$('{$this->getElementClass()}').on('click', function() {
    var id = selectedRows().join();
    if(id){
        $('.countSelect').html('<i class="fa fa-check-square-o"></i> '+selectedRows().length);
        $('.title_assign').val($('.title_assign').attr('basevalue')+selectedRows().join(', '));
        $('.assign_to').removeClass('hidden');
    }
});

$('.assign-submit').on('click', function() {
    var id = selectedRows().join();
    swal({
      title: "$title",
      text: $('.title_assign').attr('basevalue')+selectedRows().join(', '),
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "$confirm",
      closeOnConfirm: false,
      cancelButtonText: "$cancel"
    },
    function(){
        $.ajax({
            method: 'post',
            url: '{$this->resource}/workflow/' + id,
            data: {
            _method:'put',
                type:'{$this->action}',
                title:$('.title_assign').val(),
                assignableUser:$('.assignableUser').val(),
                _token:'{$this->getToken()}'
            },
            success: function (data) {
            $.pjax.reload('#pjax-container');
            if (typeof data === 'object') {
                if (data.status) {
                    swal(data.message, '', 'success');
                } else {
                    swal(data.message, '', 'error');
                }
            }
            }
        });
    });
});


EOT;
    }
}
