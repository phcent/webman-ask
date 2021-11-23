<?php
/**
 *-------------------------------------------------------------------------p*
 * 分类管理
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

class CategoryController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\CmsCategory::class;
    public  $name = '分类';
    public  $projectName = 'CMS管理-分类管理-';

    /**
     * 获取新增请求数据
     * @return array|mixed
     */
    public function getAdminCreate()
    {
        $data['categoryList'] = (new $this->model)->get();
        return $data;
    }

    /**
     * 新增数据验证
     * @param $user
     * @return array|mixed
     */
    public function beforeAdminCreate($user)
    {
        $params = phcentParams([  'name'
            ,'pid' => 0
            ,'image_name'
            ,'code'
            ,'description'
            ,'keyword'
            ,'sort' => 0
            ,'status' => 1]);
        return $params;
    }

    /**
     * 获取编辑数据
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getAdminUpdate($id)
    {
        $info =(new $this->model)->where($this->key, $id)->first();
        if ($info == null) {
            throw new \Exception('数据不存在');
        }
        $data['info'] = $info;
        $data['categoryList'] = (new $this->model)->get();
        return $data;
    }

    /**
     * 编辑验证数据
     * @param $user
     * @param $id
     * @return array|mixed
     */
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([
            'name'
            ,'pid' => 0
            ,'image_name'
            ,'code'
            ,'description'
            ,'keyword'
            ,'sort' => 0
            ,'status' => 1]);
        return $params;
    }

    /**
     * 重写删除方法
     * @param $user
     * @param $ids
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function adminDestroy($user,$ids,$id){
        $category = (new $this->model)->whereIn('id',$ids)->has('article')->get();
        if($category->count() > 0){
            throw new \Exception('删除的分类中有分类下含有文章');
        }
        (new $this->model)->destroy($ids);
        return [];
    }
}