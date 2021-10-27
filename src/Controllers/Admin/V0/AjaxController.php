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


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Logic\CodeLogic;
use support\Request;

class AjaxController
{
    public function test(Request $request,$id)
    {
        $data = [
            'controller' => $request->controller,
            'id' => $request->id,
            'action' => $request->action
        ];
        return phcentSuccess($data);
    }

    /**
     * 获取后台菜单
     * @param Request $request
     * @return \support\Response
     */
    public function menu(Request $request)
    {

        try {

        }catch (\Exception $e){
            return  phcentError($e->getMessage());
        }

        $user = AuthLogic::getInstance()->user();

        $data['site_id'] = \request()->siteId;
        $data['code'] = (new \Phcent\WebmanAsk\Logic\CodeLogic)->encode('999');
        $data['ascode'] = (new \Phcent\WebmanAsk\Logic\CodeLogic)->decode($data['code']);
        return phcentSuccess($data);
    }
}