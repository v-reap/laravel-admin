Type Process:
type_id, process_name, process_id, process_type, process_to
id
flow_id
process_name
process_type
process_to
child_id
child_relation
child_after
child_back_process
return_sponsor_ids
return_respon_ids
write_fields
secret_fields
lock_fields
check_fields
auto_person
auto_unlock
auto_sponsor_ids
auto_sponsor_text
auto_respon_ids
auto_respon_text
auto_role_ids
auto_role_text
auto_process_sponsor
range_user_ids
range_user_text
range_dept_ids
range_dept_text
range_role_ids
range_role_text
receive_type
is_user_end
is_userop_pass
is_sing
sign_look
is_back
out_condition

条件判断：

字段验证：

Update/Create:

审批过程：
if is_one
    add run: $wf_id,$wf_process['id'],$wf_fid,$wf_type,$uid
    add run_process: $wf_id,$wf_process,$wf_run,$uid
    add run_cache: $wf_run,$wf,$wf_process,$wf_fid
    update task: status=1
    add run_log: $uid,$wf_run,$config,'Send'
if back && step==0
    update run: status=1
    update run_process: status=2 remark
    update task: status=-1
if back && step!=0
    update run_process: status=2 remark
    add run_process:
    add run_log:
if ok && NotEnd
    update run_process: status=2 remark
    update run: run_flow_process=next id
    add run_process:
    add run_log:
if ok && IsEnd
    update run: status=1
    add run_log:
    update task: status=2
if sing
    add run_sign
    update run: is_sing=1
    update run_process: status=2 remark
    add run_log: Sing

