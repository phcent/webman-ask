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


use Phcent\WebmanAsk\Model\AskCategory;

use Phcent\WebmanAsk\Service\CategoryService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class CategoryController
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
            $askCategory = new AskCategory();
            $askCategory = phcentWhereParams($askCategory, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askCategory = $askCategory->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askCategory = $askCategory->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $askCategory->paginate($request->input('limit',10));
            $data['list'] = $list->items();
            $data['categoryType'] = config('phcentask.categoryType');
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
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $data['categoryType'] = config('phcentask.categoryType');
                return phcentSuccess( $data);
            }else{
                Validator::input($request->post(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('分类名称'),
                    'sort' => Validator::digit()->min(0)->setName('权重'),
                    'type' => Validator::digit()->in([1,2,3,4])->setName('所属'),
                    'status' => Validator::digit()->in([1,2])->setName('状态'),
                ]);
                $params = phcentParams([
                    'name',
                    'icon',
                    'sort' => 0,
                    'keyword',
                    'description',
                    'type' => 1,
                    'status' => 1
                ]);
                Db::connection()->beginTransaction();
                AskCategory::create($params);
                CategoryService::delCache();
                Db::connection()->commit();
                return phcentSuccess();

            }
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
                $data['categoryType'] = config('phcentask.categoryType');
                $info = AskCategory::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('分类不存在');
                }
                $data['info'] = $info;
                return phcentSuccess( $data);
            }else{
                Validator::input($request->all(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('分类名称'),
                    'sort' => Validator::digit()->min(0)->setName('权重'),
                    'type' => Validator::digit()->in([1,2,3,4])->setName('所属'),
                    'status' => Validator::digit()->in([1,2])->setName('状态'),
                ]);
                $params = phcentParams([
                    'name',
                    'icon',
                    'sort',
                    'keyword',
                    'description',
                    'type',
                    'status',
                    'color'
                ]);
                $info = AskCategory::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('问题不存在');
                }
                Db::connection()->beginTransaction();
                foreach ($params as $k=>$v){
                    $info->$k = $v;
                }
                $info->save();
                CategoryService::delCache();
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
            AskCategory::destroy($ids);
            CategoryService::delCache();
            Db::connection()->commit();
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}