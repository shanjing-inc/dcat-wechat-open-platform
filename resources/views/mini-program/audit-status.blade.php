@if($status == 0)
    <span class="badge badge-success">审核成功</span>
@elseif($status == 1)
    <span class="badge badge-danger">审核被拒绝</span>
@elseif($status == 2)
    <span class="badge badge-primary">审核中</span>
@elseif($status == 3)
    <span class="badge badge-secondary">已撤回</span>
@elseif($status == 4)
    <span class="badge badge-warning">审核延后</span>
@else
    <span class="badge badge-danger">未知</span>
@endif
