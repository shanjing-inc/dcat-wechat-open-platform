<?php

namespace Shanjing\DcatWechatOpenPlatform\Http\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Shanjing\DcatWechatOpenPlatform\Actions\CreateAuthorizerAction;
use Shanjing\DcatWechatOpenPlatform\Models\WechatOpenPlatform;

class WechatOpenPlatformController extends BaseAdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new WechatOpenPlatform(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('appid');
            $grid->column('secret');
            $grid->column('token');
            $grid->column('aes_key');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
                $filter->panel();
                $filter->equal('id')->width(2);
                $filter->equal('appid')->width(2);
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new CreateAuthorizerAction());
            });
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
        return Show::make($id, new WechatOpenPlatform(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('appid');
            $show->field('secret');
            $show->field('token');
            $show->field('aes_key');
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
        return Form::make(new WechatOpenPlatform(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('appid');
            $form->text('secret');
            $form->text('token');
            $form->text('aes_key');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
