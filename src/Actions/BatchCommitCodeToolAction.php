<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Modal;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\BatchCommitCodeForm;

class BatchCommitCodeToolAction extends AbstractTool
{
    public function render()
    {
        $modalForm = BatchCommitCodeForm::make();

        Admin::script(
            <<<JS
            $(document).on('click', '.batch-commit-btn', function(e) {
                var keys = Dcat.grid.selected();
                if (!keys.length) {
                    Dcat.error('请选择小程序');
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                window.__batch_commit_keys = keys.join(',');
                var input = $('#batch_commit_keys');
                if (input.length) {
                    input.val(window.__batch_commit_keys);
                }
            });
JS
        );

        return Modal::make()
            ->lg()
            ->title('批量提交代码')
            ->body($modalForm)
            ->button("<button class='btn btn-primary btn-outline ml-1 batch-commit-btn'>1.批量提交代码</button>");
    }
}
