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
        return <<<EOT
$('{$this->getElementClass()}').on('click', function() {
    var id = selectedRows().join();

});

EOT;
    }
}


//var html ='<select class="form-control select2-user" name="assigned_to" ></select>';
//swal({
//        title: "Messaggio",
//        html: html,
//        confirmButtonColor: '#26C281',
//        confirmButtonText: 'Salva',
//        showCancelButton: true,
//        cancelButtonText: 'Chiudi',
//        cancelButtonColor: '#EF4836',
//        focusConfirm: false,
//        onOpen: function () {
//    $('.select2-user').select2();
//},
//    },
//    function(){
//        $.ajax({
//            method: 'post',
//            url: '{$this->resource}/workflow/' + id,
//            data: {
//            _method:'put',
//                type:'{$this->action}',
//                _token:'{$this->getToken()}'
//            },
//            success: function (data) {
//            $.pjax.reload('#pjax-container');
//            if (typeof data === 'object') {
//                if (data.status) {
//                    swal(data.message, '', 'success');
//                } else {
//                    swal(data.message, '', 'error');
//                }
//            }
//            }
//        });
//    });