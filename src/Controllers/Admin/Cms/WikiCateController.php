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

namespace Phcent\WebmanAsk\Controllers\Admin\Cms;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;

class WikiCateController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\CmsWikiCate::class;
    public  $name = '文档分类';
    public  $projectName = 'CMS管理-文档分类管理-';

    public function beforeAdminCreate($user)
    {
        $params = phcentParams([
            'name',
            'image_name',
            'content'
        ]);
        return $params;
    }

    public function beforeAdminUpdate($user, $id)
    {
        return phcentParams([
            'name',
            'image_name',
            'content']);
    }
}