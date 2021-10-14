<?php
/**
 *-------------------------------------------------------------------------p*
 * 签到日志
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


use Phcent\WebmanAsk\Model\AskSingninLog;

use support\Request;

class SigninController
{
    /**
     * 签到日志
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $askSingninLog = new AskSingninLog();
            $askSingninLog = phcentWhereParams($askSingninLog, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askSingninLog = $askSingninLog->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askSingninLog = $askSingninLog->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $askSingninLog->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }
}