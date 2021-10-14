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
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */
namespace Phcent\WebmanAsk\Controllers\User\V1;

use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskReport;
use Phcent\WebmanAsk\Service\ReportService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class ReportController
{
    /**
     * 获取列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
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

    /**
     * 发布举报
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $data['reasonList'] = config('phcentask.reasonList');
                $data['reportType'] = config('phcentask.reportType');
                return phcentSuccess( $data);
            }else{
                $userId = AuthLogic::getInstance()->userId();
                if(empty($userId)){
                    throw new \Exception('请先登入');
                }
                Validator::input($request->post(), [
                    'reason' => Validator::length(1, 32)->noWhitespace()->setName('举报原因'),
                    'type' => Validator::digit()->in([1,2,3,4,5])->setName('举报类型'),
                    'theme_id' => Validator::digit()->min(1)->setName('项目编号'),
                ]);
                $params = phcentParams(['reason', 'type', 'theme_id']);
                Db::connection()->beginTransaction();
                ReportService::create($params,$userId);
                Db::connection()->commit();
                return phcentSuccess();
            }
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }
}