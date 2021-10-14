<?php
/**
 *-------------------------------------------------------------------------p*
 * 积分日志
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


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\PointsLog;
use support\Request;

class PointsController
{
    /**
     * 获取积分日志
     * @param Request $request
     * @return \support\Response
     * @throws \Exception
     */
    public function log(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            $pointsLog = new PointsLog();
            $pointsLog = phcentWhereParams($pointsLog, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $pointsLog = $pointsLog->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $pointsLog = $pointsLog->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $pointsLog->where('user_id',$userId)->paginate($request->input('limit',10));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }

    }
}