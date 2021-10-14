<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答会员管理
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


use Phcent\WebmanAsk\Model\AskUser;

use support\Db;
use support\Request;

class UserController
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
            $askUser = new AskUser();
            $askUser = phcentWhereParams($askUser, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askUser = $askUser->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askUser = $askUser->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            if($request->input('dataRecovery')){
                $askUser = $askUser->onlyTrashed();
            }
            $list = $askUser->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
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
                $info = AskUser::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('会员不存在');
                }
                $data['info'] = $info;
                return phcentSuccess($data);
            }
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }
        try {
            $params = phcentParams([
                'question_num',
                'answer_num',
                'article_num',
                'answer_num',
                'collection_num',
                'follow_num',
                'view_num',
                'fans_num',
                'is_expert',
                'grade_id',
                'answer_best_num',
                'description',
                'hot_sort',
                'is_admin',
                'user_name',
            ]); //允许修改字段
            $info = AskUser::where('id', $id)->first();
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
}