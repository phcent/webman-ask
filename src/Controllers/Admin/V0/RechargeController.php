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
 * @since      象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\V0;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;

class RechargeController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\SysBalanceLog::class;
    public  $name = '充值';
    public  $projectName = '系统管理-充值管理-';
}