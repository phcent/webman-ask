<?php
/**
 *-------------------------------------------------------------------------p*
 * 获取配置项目
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

use Phcent\WebmanAsk\Model\SysConfig;
use support\Redis;

class ConfigService
{
    /**
     * 获取配置项目列表
     * @return mixed
     */
    public static function getList()
    {
        $siteId = request()->siteId;
        $list = Redis::get("phcentAskConfig{$siteId}");
        if($list == null){
            $list = collect();
            $listSetting = SysConfig::where('site_id',$siteId)->get();
            foreach ($listSetting as $key => $setting) {
                $list->put($setting->key,$setting->value);
            }
            $list = $list->toJson();
            Redis::set("phcentAskConfig{$siteId}",$list);
        }
        return json_decode($list);
    }

    /**
     * 根据KEY获取值
     * @param $key
     * @return mixed
     */
    public static function getByKey($key) {
        $listSetting = self::getList();
        foreach ($listSetting as $k => $v) {
            if ($key == $k) {
                return $v;
            }
        }
    }

    /**
     * 查询指定列
     * @param array $keys
     * @return array
     */
    public static function getRow($keys = []) {
        $listSetting = [];
        foreach ($keys as $key) {
            $listSetting[$key] = self::getByKey($key);
        }
        return $listSetting;
    }
}