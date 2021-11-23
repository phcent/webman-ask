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
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk;


use Webman\Route;

class AskRoute extends Route
{

    public static function resource(string $name, string $controller, array $options = [])
    {
       return static::group($name,function () use ($controller, $options) {
           foreach ($options as $action) {
               static::any("/{$action}[/{id}]",[$controller,$action]);
           }
       });
    }
}