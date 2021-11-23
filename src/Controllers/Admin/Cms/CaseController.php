<?php
/**
 *-------------------------------------------------------------------------p*
 * 案例
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP 知识付费问答系统-CMS管理
 *-------------------------------------------------------------------------t*
 */

namespace Phcent\WebmanAsk\Controllers\Admin\Cms;

use Phcent\WebmanAsk\Logic\AdminControllerLogic;


class CaseController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\CmsCase::class;
    public  $name = '案例';
    public  $projectName = 'CMS管理-案例管理-';

    public function beforeAdminCreate($user)
    {
        $params = phcentParams([  'name',
            'image',
            'company',
            'content',
            'summary',
            'goods',
            'code']);
        $params['user_id'] = $user->id;
        return $params;
    }
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([  'name',
            'image',
            'company',
            'content',
            'summary',
            'goods',
            'code']);
        $params['user_id'] = $user->id;
        return $params;
    }
}