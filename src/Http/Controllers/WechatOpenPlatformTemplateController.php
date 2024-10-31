<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use Shanjing\DcatWechatOpenPlatform\Actions\CreateTemplateAction;
use Shanjing\DcatWechatOpenPlatform\Actions\SyncTemplateAction;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;
use Shanjing\DcatWechatOpenPlatform\Repositories\WechatOpenPlatformTemplate;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatformTemplate as Model;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WechatOpenPlatformTemplateController extends BaseAdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new WechatOpenPlatformTemplate(), function (Grid $grid) {
            $grid->model()->orderByDesc('id');
            $grid->column('id')->sortable();
            $grid->column('platform_id');
            $grid->column('template_id');
            $grid->column('template_type')->using(Model::$templateTypes)->label();
            $grid->column('user_version');
            $grid->column('user_desc');
            $grid->column('category_list');
            $grid->column('audit_status')->if(function () {
                return $this->template_type == Model::TEMPLATE_TYPE_0;
            })->display(function () {
                return '-';
            })->else()->using(Model::$auditStatuses);
            $grid->column('reason');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->equal('id')->width(2);
                $filter->equal('platform_id')->width(2);
                $filter->equal('template_id')->width(2);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {

            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new CreateTemplateAction());
                $tools->append(new SyncTemplateAction());
            });

            $grid->disableBatchDelete();
            $grid->disableEditButton();
            $grid->disableCreateButton();
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new WechatOpenPlatformTemplate(), function (Show $show) {
            $show->field('id');
            $show->field('template_id');
            $show->field('template_type');
            $show->field('user_version');
            $show->field('user_desc');
            $show->field('category_list');
            $show->field('audit_status');
            $show->field('reason');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new WechatOpenPlatformTemplate(), function (Form $form) {
            $form->display('id');
            $form->text('template_id');
            $form->text('template_type');
            $form->text('user_version');
            $form->text('user_desc');
            $form->text('category_list');
            $form->text('audit_status');
            $form->text('reason');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    /**
     * 获取草稿箱
     *
     * @author Hailong Tian <tianhailong@shanjing-inc.com>
     */
    public function draftList()
    {
        $platformId = request('q');
        $platform   = WechatOpenPlatform::find($platformId);
        $records    = $platform->draftList();
        $options    = [];
        foreach ($records as $record) {
            $options[] = [
                'id'   => $record['draft_id'],
                'text' => "【{$record['user_version']}】{$record['user_desc']}",
            ];
        }

        return $options;
    }
}
