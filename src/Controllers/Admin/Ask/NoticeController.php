<?php
/**
 *-------------------------------------------------------------------------p*
 * 会员通知
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\Ask;
use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use support\Request;

class NoticeController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskNotice::class;
    public  $name = '通知';
    public  $projectName = '问答管理-通知管理';


    /**
     * 新增
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {

    }
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([
            'user_id',
            'from_user_id',
            'title',
            'content',
            'type',
            'theme_id',
            'is_read',
            'operation_stage'
        ]);
        return $params; // TODO: Change the autogenerated stub
    }
}