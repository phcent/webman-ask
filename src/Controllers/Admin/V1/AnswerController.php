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


use Phcent\WebmanAsk\Logic\AskCommentLogic;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskReply;

use support\Db;
use support\Request;

class AnswerController
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
            $askComment = new AskAnswer();
            $askComment = phcentWhereParams($askComment, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askComment = $askComment->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askComment = $askComment->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            if($request->input('dataRecovery')){
                $askComment = $askComment->onlyTrashed();
            }
            $list = $askComment->paginate($request->limit ?? 10);
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
     */
    public function create(Request $request)
    {
        return phcentError();

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
                $info = AskAnswer::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('问答不存在');
                }
                $data['info'] = $info;
              //  $data['category'] = AskCategory::where('type',1)->get();
                return phcentSuccess($data);
            }
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }
        try {
            $params = phcentParams([
                'reward_balance',
                'reward_points',
                'content',
                'digg_num',
                'step_num',
                'pay_num',
                'share_num',
                'report_num',
                'collection_num',
                'thank_num',
                'reply_num',
                'reward_time',
                'user_id',
                'status'
            ]); //允许修改字段
            $info = AskAnswer::where('id', $id)->first();
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
     * 删除回答
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
            //删除回答
            $askComment = AskAnswer::whereIn('id', $ids)->get();
            AskAnswer::destroy($askComment->pluck('id'));

            //删除评论
            $askCommentReply = AskReply::whereIn('theme_id', $askComment->pluck('id'))->where('type', 1)->get();
            AskReply::destroy($askCommentReply->pluck('id'));

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
                foreach ($ids as $value) {
                    AskCommentLogic::deleteAnswer($value);
                }
            }else{
                $askComment = AskAnswer::whereIn('id',$ids)->onlyTrashed()->get();
                foreach ($askComment as $item) {
                    $item->restore();
                }
            }
            Db::connection()->commit();
            return phcentSuccess();
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}