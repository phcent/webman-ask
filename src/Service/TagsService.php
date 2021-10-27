<?php
/**
 *-------------------------------------------------------------------------p*
 * 站点管理
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

namespace Phcent\WebmanAsk\Service;

use Phcent\WebmanAsk\Model\AskTags;
use support\Db;

class TagsService
{
    /**
     * 更新话题内容
     * @param $params
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function updateTags($params,$id,$userId)
    {
        try {
            $siteId = request()->siteId;
            $info = AskTags::where('id',$id)->where('site_id',$siteId)->first();
            if($info == null){
                throw new \Exception('问题不存在');
            }
            //判断是否有修改权限
            $haveRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
            if(!$haveRole){
                throw new \Exception('无权限修改');
            }
            Db::connection()->beginTransaction();
            foreach ($params as $k=>$v){
                $info->$k = $v;
            }
            $info->save();
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}