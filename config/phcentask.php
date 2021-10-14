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
 
return [
    'code' => [
        'intel_yes'  => 200,
        'intel_no' => 400,
        'intel_no_login' => 401,
        'intel_expire' => 402,
        'intel_authority' => 403,
        'intel_bad' => 404,
    ],
    'jwt' => [
        'iss' => 'phcent-ask',
        'aud' => 'phcent-ask',
        'key' => 'w5LgNx5luRRjmamZFSqz3cPHOp9KuQPExlvgi18DN4SdnSI9obcVEhiZVE0NIIC7',
        'exp' => 604800, # 过期时间
    ],
    'cross' => [
        'origin' => '*',
        'methods' => 'GET,POST,PUT,DELETE,OPTIONS',
        'headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin'
    ]
];