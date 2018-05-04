

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

