<?php
/**
 *-------------------------------------------------------------------------p*
 * 文档管理
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP 知识付费问答系统-CMS管理
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\Cms;

use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Model\CmsWikiCate;
use Phcent\WebmanAsk\Model\CmsWikiOld;
use Phcent\WebmanAsk\Service\CmsWikiCateService;
use Phcent\WebmanAsk\Service\CmsWikiOldService;
use Phcent\WebmanAsk\Service\CmsWikiService;
use support\Request;

class WikiController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\CmsWiki::class;
    public  $name = '文档列表';
    public  $projectName = 'CMS管理-文档管理-';

    /**
     * 新增之前数据处理
     * @param $user
     * @return array|mixed
     */
    public function beforeAdminCreate($user)
    {
        $params = phcentParams([  'name',
            'pid',
            'content',
            'cate_id',
            'role']);
        $params['user_id'] = $user->id;
        $params['user_name'] = $user->nick_name;
        return $params;
    }

    /**
     * 修改之前数据处理
     * @param $user
     * @param $id
     * @return array|mixed
     */
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([  'name',
            'pid',
            'content',
            'cate_id',
            'role']);
        $params['user_id'] = $user->id;
        $params['user_name'] = $user->nick_name;
        return $params;
    }

    /**
     * 插入新增时事务处理
     * @param $user
     * @param $create
     * @return bool|void
     */
    public function insertAdminCreate($user, $create)
    {
        //写入历史记录
        CmsWikiOld::create([
            'name' => $create->name,
            'wiki_id' => $create->id,
            'content' => $create->content,
            'user_name' => $create->user_name,
            'user_id' => $create->user_id,
            'role' => $create->role
        ]);
    }

    /**
     * 修改数据事务同步
     * @param $user
     * @param $params
     * @param $info
     * @param $id
     */
    public function insertAdminUpdate($user, $params, $info, $id)
    {
        //写入历史记录
        CmsWikiOld::create([
            'name' => $info->name,
            'wiki_id' => $id,
            'content' => $info->content,
            'user_name' => $info->user_name,
            'user_id' => $info->user_id,
            'role' => $info->role
        ]);
    }

    /**
     * 获取修改数据插入
     * @param $info
     * @param $id
     * @return mixed
     */
    public function insertGetAdminUpdate($info, $id)
    {
        $data['info'] = $info;
        $data['categoryList'] = CmsWikiCate::get();
        return $data;
    }

    /**
     * 删除数据
     * @param $user
     * @param $ids
     * @param $id
     * @return array
     */
    public function adminDestroy($user, $ids, $id)
    {
        $wikiList = (new $this->model)->whereIn('id',$ids)->with('child')->get();
        CmsWikiService::delWikiChild($wikiList);
        return [];
    }


    /**
     * 获取文档分类列表
     * @param Request $request
     * @return \support\Response
     */
    public function cateList(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $model = new CmsWikiCate();
            $model = phcentWhereParams($model, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $model = $model->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $model = $model->orderBy('id', 'desc');
            }
            $list = $model->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 新增文档分类
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function cateCreate(Request $request)
    {
        try {
            phcentMethod(['POST']);
            $params = phcentParams([
                'name',
                'image_name',
                'content'
            ]);
            CmsWikiCateService::create($params);
            return phcentSuccess();

        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 修改文档分类
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function cateUpdate(Request $request,$id)
    {
        try {
            phcentMethod(['GET','PUT']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('操作编号异常');
            }
            if($request->method() == 'GET'){
                $info = CmsWikiCate::where('id',$id)->first();
                if($info == null){
                    throw new \Exception('文档分类不存在');
                }
                $data['info'] = $info;
                return phcentSuccess($data);
            }else{
                $params = phcentParams([
                    'name',
                    'image_name',
                    'content'
                ]);
                CmsWikiCateService::update($params,$id);
                return phcentSuccess();
            }
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }
    /**
     * 删除文档
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function cateDestroy(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            phcentMethod(['DELETE']);
            CmsWikiCateService::destroy($ids);
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            return phcentError($e->getMessage());
        }
    }

    /**
     * 复制文档
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function copy(Request $request,$id)
    {
        try {
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号异常');
            }
            phcentMethod(['POST']);
            CmsWikiService::copyWiki($id);
            return phcentSuccess('复制成功');
        } catch (\Exception $e) {
            return phcentError($e->getMessage());
        }
    }

    /**
     * 复制文档树
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function copyAll(Request $request,$id)
    {
        try {
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号异常');
            }
            phcentMethod(['POST']);
            CmsWikiService::copyAllWiki($id);
            return phcentSuccess('复制成功');
        } catch (\Exception $e) {
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取文档历史记录
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function oldList(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            $model = new CmsWikiOld();
            $model = phcentWhereParams($model, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $model = $model->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $model = $model->orderBy('id', 'desc');
            }
            $list = $model->where('wiki_id',$id)->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 删除文档历史记录
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function oldDestroy(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            phcentMethod(['DELETE']);
            CmsWikiOldService::destroy($ids);
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            return phcentError($e->getMessage());
        }
    }

    /**
     * 恢复历史记录
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function oldRecovery(Request $request,$id)
    {
        try {
            phcentMethod(['POST']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号异常');
            }
            CmsWikiOldService::recovery($id);
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            return phcentError($e->getMessage());
        }
    }

}