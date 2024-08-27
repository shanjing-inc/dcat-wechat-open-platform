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
            <p class="card-text">版本号：</p>
            <p class="card-text">发布时间：</p>
            <p class="card-text">版本描述：</p>
            {!! $rollbackBtn !!}
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="">审核版本</h3>
            <hr>
            <p class="card-text">暂无提交审核的版本或者版本已发布上线</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="">体验版本</h3>
            <hr>
            <p class="card-text">尚未提交体验版</p>
            {!! $modalBtn !!}
        </div>
    </div>
</div>
