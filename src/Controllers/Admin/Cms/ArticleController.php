<?php
/**
 *-------------------------------------------------------------------------p*
 * 文章管理
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
use Phcent\WebmanAsk\Model\CmsCategory;
use support\Request;

class ArticleController extends AdminControllerLogic
{

    public  $model = \Phcent\WebmanAsk\Model\CmsArticle::class;
    public  $name = '文章';
    public  $projectName = 'CMS管理-文章管理-';

    public function beforeAdminCreate($user)
    {
        $params = phcentParams([ 'name',
            'image_name',
            'content',
            'cate_id' => 0,
            'keyword',
            'description',
            'status' =>1,
            'sort' =>0,]);
        $params['user_id'] = $user->id;
        $params['user_name'] = $user->nick_name;
        return $params;
    }
    public function beforeAdminUpdate($user, $id)
    {
        return phcentParams(['name',
            'image_name',
            'content',
            'cate_id',
            'keyword',
            'description',
            'status',
            'sort',]);
    }
    public function getAdminCreate(){
        $data['category'] = CmsCategory::where('status',1)->get();
        return $data;
    }
    public function getAdminUpdate($id){
        $info = (new $this->model)->where($this->key, $id)->first();
        if ($info == null) {
            throw new \Exception('数据不存在');
        }
        $data['info'] = $info;
        $data['categoryList'] = CmsCategory::where('status',1)->get();
        return $data;
    }

    /**
     * 获取分类
     * @param Request $request
     * @return \support\Response
     */
    public function cate(Request $request)
    {
        try {
            $data['cateList'] = CmsCategory::where('status',1)->get();
            return phcentSuccess($data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

}