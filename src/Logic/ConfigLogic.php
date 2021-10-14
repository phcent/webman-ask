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
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Logic;


class ConfigLogic
{
    const
        STATUS_YES_CODE = 200, //正确状态码
        STATUS_NO_CODE = 400, //异常状态码
        STATUS_NOT_LOGIN_CODE = 401, //未登入
        STATUS_BACK_CODE = 402, //登入过期
        STATUS_RULE_CODE = 403, //未登入

        STATUS_NO = 2, //常用2
        NUMBER_ONE = 1, //数字1
        NUMBER_TWO = 2; //数字2
}