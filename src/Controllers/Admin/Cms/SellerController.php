<?php
/**
 *-------------------------------------------------------------------------p*
 * 销售商
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

class SellerController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\CmsSeller::class;
    public  $name = '经销商';
    public  $projectName = 'CMS管理-经销商管理-';

    /**
     * 新增验证数据
     * @param $user
     * @return array|mixed
     */
    public function beforeAdminCreate($user)
    {
        $params = phcentParams([
            'user_id',
            'company_name',
            'company_scope',
            'contacts_name',
            'contacts_phone',
            'contacts_wechat',
            'goods',
            'solutions',
            'status' => 10]);
        return $params;
    }

    /**
     * 修改验证数据
     * @param $user
     * @param $id
     * @return array|mixed
     */
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([
            'user_id',
            'company_name',
            'company_scope',
            'contacts_name',
            'contacts_phone',
            'contacts_wechat',
            'goods',
            'solutions',
            'status']);
        return $params;
    }
}