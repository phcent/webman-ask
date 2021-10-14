<?php
/**
 *-------------------------------------------------------------------------p*
 * 会员动态
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

use Phcent\WebmanAsk\Model\AskDynamic;

use Respect\Validation\Validator;
use support\Db;
use support\Request;

class DynamicController
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
            $askDynamic = new AskDynamic();
            $askDynamic = phcentWhereParams($askDynamic, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askDynamic = $askDynamic->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askDynamic = $askDynamic->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $askDynamic->paginate($request->limit ?? 10);
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
                $data['dynamicType'] = config('phcentask.dynamicType');
                $info = AskDynamic::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('动态不存在');
                }
                $data['info'] = $info;
                return phcentSuccess( $data);
            }else{
                Validator::input($request->all(), [
                    'type' => Validator::digit()->in([1,2,3,4])->setName('所属'),
                ]);
                $params = phcentParams([
                    'type',
                    'theme_id',
                    'operation_stage',
                    'title',
                    'content'
                ]);
                $info = AskDynamic::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('动态不存在');
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
            AskDynamic::destroy($ids);
            Db::connection()->commit();
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}