<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答话题模块
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
use Phcent\WebmanAsk\Model\AskTags;

use Respect\Validation\Validator;
use support\Db;
use support\Request;

class TagsController
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
            $askTags = new AskTags();
            $askTags = phcentWhereParams($askTags, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askTags = $askTags->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askTags = $askTags->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            if($request->input('dataRecovery')){
                $askTags = $askTags->onlyTrashed();
            }
            $list = $askTags->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 新增数据 支持get,post类型
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $data['category'] = AskCategory::where('type',3)->get();
                return phcentSuccess( $data);
            }else{
                Validator::input($request->post(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('标签名称'),
                    'status' => Validator::digit()->in([1,2])->setName('状态'),
                ]);
                $params = phcentParams([
                    'name' => '',
                    'icon',
                    'image',
                    'keyword',
                    'description',
                    'question_num' => 0,
                    'article_num' => 0,
                    'follow_num' => 0,
                    'report_num' =>0,
                    'hot_sort' => 0,
                    'status' => 1
                ]);
                Db::connection()->beginTransaction();
                AskTags::create($params);
                Db::connection()->commit();
                return phcentSuccess();

            }
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 修改内容
     * @param Request $request
     * @param null $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['GET','PUT']);
            if($request->method() == 'GET'){
                $info = AskTags::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('话题不存在');
                }
                $data['info'] = $info;
                $data['category'] = AskCategory::where('type',3)->get();
                return phcentSuccess($data);
            }
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }
        try {
            $params = phcentParams([
                'name',
                'icon',
                'image',
                'keyword',
                'description',
                'question_num',
                'article_num',
                'follow_num',
                'report_num',
                'hot_sort',
                'status'
            ]); //允许修改字段
            $info = AskTags::where('id', $id)->first();
            if ($info == null) {
                throw new \Exception('问题不存在');
            }
            Db::connection()->beginTransaction();
            foreach ($params as $k=>$v){
                $info->$k = $v;
            }
            $info->save();
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 删除话题
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
            AskTags::destroy($ids);

            Db::connection()->commit();
            return phcentSuccess([],'删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 回收站删除与还原
     * @param $id
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function recovery(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            phcentMethod(['DELETE','PUT']);
            Db::connection()->beginTransaction();
            if($request->method() == 'DELETE') {
                AskTags::whereIn('id',$ids)->forceDelete();
            }else{
                $askComment = AskTags::whereIn('id',$ids)->onlyTrashed()->get();
                foreach ($askComment as $item) {
                    $item->restore();
                }
            }
            Db::connection()->commit();
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

}