<?php
/**
 *-------------------------------------------------------------------------p*
 *
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\Ask;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Service\ConfigService;

class ThanksController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskThanks::class;
    public  $name = '感谢';
    public  $projectName = '问答管理-感谢管理-';

    public function beforeAdminIndex($model)
    {
        $model = $model->with(['user','toUser','order']);
        return $model;
    }
    public function afterAdminIndex($list)
    {
        $list->map(function ($item){
            $item->user_name = $item->user!=null?$item->user->nick_name : '';
            $item->to_user_name = $item->toUser != null ? $item->toUser->nick_name : '';
            $item->setHidden(['user','toUser']);
        });
        $data['list'] = $list->items();
        $data['typeList'] = ConfigService::getKeyName([1,2,3],config('phcentask.allType'));
        return $data;
    }
}