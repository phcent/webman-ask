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


namespace Phcent\WebmanAsk\Controllers\User\V1;


class RechargeController
{
    /**
     * 获取签到日志
     */
    public function index()
    {
        try {
            phcentMethod(['GET']);
            $askReport = new AskReport();
            $askReport = phcentWhereParams($askReport, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askReport = $askReport->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askReport = $askReport->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $askReport->paginate($request->input('limit',10));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }
}