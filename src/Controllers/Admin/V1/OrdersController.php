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


namespace Phcent\WebmanAsk\Controllers\Admin\V1;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use support\Request;

class OrdersController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\SysOrders::class;
    public  $name = '订单';
    public  $projectName = '问答管理-订单管理-';

    public function destroy(Request $request, $id)
    {
        return [];
    }
    public function update(Request $request, $id)
    {
        return []; // TODO: Change the autogenerated stub
    }
    public function create(Request $request)
    {
        return [];
    }
}