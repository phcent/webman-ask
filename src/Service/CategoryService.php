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


namespace Phcent\WebmanAsk\Service;


use Phcent\WebmanAsk\Model\AskCategory;
use support\bootstrap\Redis;

class CategoryService
{
    /**
     * 根据类型获取分类列表
     * @param $type
     * @return
     */
    public static function getCategoryList($type)
    {
        $category = Redis::get('phcentAskCategory');
        if($category == null){
            $category = AskCategory::get()->toJson();
            Redis::set('phcentAskCategory',$category);
        }
        $category = json_decode($category);
        return collect($category)->where('type',$type)->where('status',1)->all();
    }

    /**
     * 清除缓存
     */
    public static function delCache()
    {
        Redis::del(['phcentAskCategory']);
    }
}