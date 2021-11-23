<?php
/**
 *-------------------------------------------------------------------------p*
 * 案例接口
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Web\Cms;

use Phcent\WebmanAsk\Model\CmsCase;
use support\Request;

class CaseController
{
    /**
     * 获取案例列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $case = new CmsCase();
            $case = phcentWhereParams($case, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $case = $case->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $case = $case->orderBy('id', 'desc');
            }
            $list = $case->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取案例详情
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function show(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            $info = CmsCase::where('id',$id)->first();
            if($info == null){
                throw new \Exception('案例不存在');
            }
            return phcentSuccess(['info'=>$info]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}