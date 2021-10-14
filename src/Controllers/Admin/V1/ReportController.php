<?php
/**
 *-------------------------------------------------------------------------p*
 * 举报管理
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

use Phcent\WebmanAsk\Model\AskReport;

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
            $list = $askReport->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
            $data['reasonList'] = config('phcentask.reasonList');
            $data['reportType'] = config('phcentask.reportType');
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 新增
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {

    }

    /**
     * 修改分类
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $info = AskReport::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('站内信不存在');
                }
                $data['reasonList'] = config('phcentask.reasonList');
                $data['reportType'] = config('phcentask.reportType');
                $data['info'] = $info;
                return phcentSuccess( $data);
            }else{
                $params = phcentParams([
                    'reason',
                    'status',
                    'theme_id',
                    'type',
                    'content',
                    'ip',
                    'user_agent',
                    'user_id'
                ]);
                $info = AskReport::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('通知不存在');
                }
                Db::connection()->beginTransaction();
                foreach ($params as $k=>$v){
                    $info->$k = $v;
                }
                $info->save();
                Db::connection()->commit();
                return phcentSuccess();
            }
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 删除
     * @param $id
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function destroy(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            Db::connection()->beginTransaction();
            AskReport::destroy($ids);
            Db::connection()->commit();
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}