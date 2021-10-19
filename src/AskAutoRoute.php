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


namespace Phcent\WebmanAsk;


use Phcent\WebmanAsk\Logic\ConfigLogic;
use Phcent\WebmanAsk\Model\SysUser;
use support\Request;
use Webman\App;
use Webman\Route;

class AskAutoRoute
{
    /**
     * 路由注册
     */
    static function load()
    {
        Route::group('/api', function () {
            collect(glob(app_path().'/phcent/*/routes.php'))->map(function($filename) {
                Route::group('/extend',function () use ($filename) {
                    return include_once $filename;
                });
            });
            Route::group('/v0', function () {
                Route::group('/ad',function (){
                   self::AddRouter('/ajax',Controllers\Admin\V0\AjaxController::class,['test']); //操作
                    self::AddRouter('/cash',Controllers\Admin\V0\CashController::class,['index','update']); //操作
                })->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
            });
            //管理后台路由
            Route::group('/{version}/admin', function (){
                Route::any('/{controller}/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null){
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\Admin\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    $request->action = $action;
                    return call_user_func([$controller, $action], $request, $id);
                })->middleware([\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class]);
//                Route::group("/{controller}", function () {
//                    Route::any("/{action}/{id}", function ($request, $verion, $controller, $action,$id = null) {
//                        $class_name = 'Phcent\\WebmanAsk\\Controllers\\Admin\\' . Ucfirst($verion) . '\\' . Ucfirst($controller) . 'Controller';
//                        if (!is_dir(realpath(__DIR__ . '/Controllers/Admin/' . Ucfirst($verion)))) {
//                            return PhcentAsk::json(config('phcentask.code.intel_no'), $verion . '目录不存在');
//                        }
//                        if (!class_exists($class_name)) {
//                            return PhcentAsk::json(config('phcentask.code.intel_no'), $verion . "目录下的控制器: {$controller}不存在!");
//                        }
//                        if (!method_exists($class_name, $action)) {
//                            return PhcentAsk::json(config('phcentask.code.intel_no'), $verion . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
//                        }
//                        $controller = new $class_name;
//                        $request->controller = $class_name;
//                        return call_user_func([$controller, $action],$request);
//                    });
//
//                });
            });
            //会员路由
            Route::group('/{version}/user', function () {
                Route::any('/{controller}/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\SysUser\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    return call_user_func([$controller, $action], $request, $id);
                });
            })->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class);
            //前台路由
            Route::group('/{version}/web', function () {
                Route::any('/{controller}/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\Web\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    return call_user_func([$controller, $action], $request, $id);
                });
            });
        })->middleware(\Phcent\WebmanAsk\Middleware\AccessControllerMiddleware::class);

        Route::options('/api/{all:.*}', function () {
            return response('不要淘气哦！路由不存在啦');
        });
        Route::fallback(function () {
            return phcentJson(config('phcentask.code.intel_bad'), '不要淘气哦！链接不存在啦');
        });
    }
    static function AddRouter($prefix,$controller,$actions,$route)
    {
        return Route::group($prefix, function () use ($controller, $actions) {
            foreach ($actions as $k) {
                Route::any("/{$k}[/{id}]", [$controller, $k]);
            }
        });
    }
}