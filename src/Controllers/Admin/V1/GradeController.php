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


namespace Phcent\WebmanAsk\Controllers\Admin\V1;


use Phcent\WebmanAsk\Model\AskGrade;

use Respect\Validation\Validator;
use support\Db;

class GradeController
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
            $askGrade = new AskGrade();
            $askGrade = phcentWhereParams($askGrade, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askGrade = $askGrade->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askGrade = $askGrade->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $askGrade->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
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
        try {
            phcentMethod(['POST']);
            Validator::input($request->post(), [
                'name' => Validator::length(1, 32)->noWhitespace()->setName('等级名称'),
                'points' => Validator::digit()->min(1)->setName('所需积分'),
            ]);
            $params = phcentParams([
                'name',
                'image_name',
                'points'
            ]);
            Db::connection()->beginTransaction();
            AskGrade::create($params);
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
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
            phcentMethod(['GET','PUT']);
            if($request->method() == 'GET'){
                $info = AskGrade::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('等级不存在');
                }
                $data['info'] = $info;
                return phcentSuccess( $data);
            }else{
                Validator::input($request->put(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('等级名称'),
                    'points' => Validator::digit()->min(1)->setName('所需积分'),
                ]);
                $params = phcentParams([
                    'name',
                    'image_name',
                    'points'
                ]);
                $info = AskGrade::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('等级不存在');
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
            AskGrade::destroy($ids);

            Db::connection()->commit();
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}