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


use support\Redis;
use support\Request;

class SettingController
{
    /**
     * 清理缓存
     * @param Request $request
     */
    public function cache(Request $request)
    {
        $params = phcentParams(['']);
        if(empty($params)){
            Redis::del(['phcentAskNewQuestion10','phcentAskHotQuestion10','phcentAskHotTags50','phcentAskExpert5']);
        }
    }
}