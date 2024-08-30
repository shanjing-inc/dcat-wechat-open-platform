<style>
    .card {
        box-shadow: none;
    }
</style>

<div class="mt-2">
    <div class="card">
        <div class="card-body">
            <h3 class="">线上版本</h3>
            <hr>
            @if(empty($versionInfo['release_info']))
                <p class="card-text">尚未提交线上版本</p>
            @else
                <p class="card-text">版本号：{{ $versionInfo['release_info']['release_version'] }}</p>
                <p class="card-text">发布时间：{{ date('Y/m/d H:i:s', $versionInfo['release_info']['release_time']) }}</p>
                <p class="card-text">版本描述：{{ $versionInfo['release_info']['release_desc'] }}</p>
                {!! $versionInfo['release_info']['rollback_btn'] !!}
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="">审核版本</h3>
            <hr>
            @if(empty($versionInfo['audit_info']))
                <p class="card-text">暂无提交审核的版本或者版本已发布上线</p>
            @else
                <p class="card-text">版本号：{{ $versionInfo['audit_info']['user_version'] }}</p>
                <p class="card-text">审核 ID：{{ $versionInfo['audit_info']['auditid'] }}</p>
                <p class="card-text">提交时间：{{ date('Y/m/d H:i:s', $versionInfo['audit_info']['submit_audit_time']) }}</p>
                <p class="card-text">版本描述：{{ $versionInfo['audit_info']['user_desc'] }}</p>
                <p class="card-text">审核状态：@include('mini-program.audit-status', ['status' => $versionInfo['audit_info']['status']])</p>
                @if($versionInfo['audit_info']['status'] == 1)
                    <p class="card-text">驳回原因：{{ $versionInfo['audit_info']['reason'] }}</p>
                @endif
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="">体验版本</h3>
            <hr>
            @if(empty($versionInfo['exp_info']))
                <p class="card-text">尚未提交体验版</p>
            @else
                <p class="card-text">版本号：{{ $versionInfo['exp_info']['exp_version'] }}</p>
                <p class="card-text">发布时间：{{ date('Y/m/d H:i:s', $versionInfo['exp_info']['exp_time']) }}</p>
                <p class="card-text">版本描述：{{ $versionInfo['exp_info']['exp_desc'] }}</p>
                {!! $versionInfo['exp_info']['submit_audit_btn'] !!}
            @endif
            {!! $commitModalBtn !!}
        </div>
    </div>
</div>
