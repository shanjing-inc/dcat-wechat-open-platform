<?php

namespace Shanjing\DcatWechatOpenPlatform\Actions;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Modal;
use Shanjing\DcatWechatOpenPlatform\Forms\MiniProgram\BatchSubmitAuditForm;

class BatchSubmitAuditToolAction extends AbstractTool
{
    public function render()
    {
        $modalForm = BatchSubmitAuditForm::make();
        $renderUrl = $modalForm->getUrl();

        Admin::script(
            <<<JS
            $(document).on('click', '.batch-submit-audit-btn', function(e) {
                var keys = Dcat.grid.selected();
                if (!keys.length) {
                    Dcat.error('请选择小程序');
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                window.__batch_submit_audit_keys = keys.join(',');
                var input = $('#batch_submit_audit_keys');
                if (input.length) {
                    input.val(window.__batch_submit_audit_keys);
                }
            });
JS
        );

        return Modal::make()
            ->xl()
            ->title('批量提交审核')
            ->body($modalForm)
            ->onShow(
                <<<JS
                var keys = Dcat.grid.selected();
                if (!keys.length) {
                    return;
                }
                var url = '{$renderUrl}';
                url += (url.indexOf('?') === -1 ? '?' : '&') + 'keys=' + encodeURIComponent(keys.join(','));
                target.off('modal:load');
                body.html('<div style="min-height:150px"></div>').loading();
                Dcat.helpers.asyncRender(url, function (html) {
                    body.html(html);
                    target.trigger('modal:loaded');
                });
JS
            )
            ->button("<button class='btn btn-primary btn-outline ml-1 batch-submit-audit-btn'>3.批量提交审核</button>");
    }
}
