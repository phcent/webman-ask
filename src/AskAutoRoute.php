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


namespace Phcent\WebmanAsk;


use Phcent\WebmanAsk\Logic\ConfigLogic;
use Phcent\WebmanAsk\Model\User;
use support\Request;
use Webman\Route;

class AskAutoRoute
{
    static function load()
    {
//        Route::any('/api/v1/admin', function ($request) {
//            return response('test');
//        });
        Route::group('/api', function () {
            collect(glob(app_path().'/phcent/*/routes.php'))->map(function($filename) {
                Route::group('/extend',function () use ($filename) {
                    return include_once $filename;
                });
            });
//            Route::group('/v0', function () {
//                Route::group('/admin', function () {
//                    phcentAddRouter('/album',Controllers\Admin\V0\AlbumController::class,['index','update','destroy','recovery']); //回答
//                    phcentAddRouter('/cash',Controllers\Admin\V0\CashController::class,['index','update']); //提现
//                    phcentAddRouter('/log',Controllers\Admin\V0\LogController::class,['index','update']); //操作日志
//                    phcentAddRouter('/team',Controllers\Admin\V0\TeamController::class,['index','update','destroy','recovery']); //管理组
//                    phcentAddRouter('/user',Controllers\Admin\V0\UserController::class,['index','update','destroy','recovery']); //会员
//                })->middleware(Middleware\AdminAuthMiddleware::class);
//            });
//            Route::group('/v1', function () {
//                Route::group('/admin', function () {
//                    phcentAddRouter('/answer',Controllers\Admin\V1\AnswerController::class,['index','update','destroy','recovery']); //回答
//                    phcentAddRouter('/article',Controllers\Admin\V1\ArticleController::class,['index','update','destroy','recovery']); //文章
//                    phcentAddRouter('/category',Controllers\Admin\V1\CategoryController::class,['index','update','create','destroy']); //分类
//                    phcentAddRouter('/dynamic',Controllers\Admin\V1\DynamicController::class,['index','update','destroy']); //动态
//                    phcentAddRouter('/grade',Controllers\Admin\V1\GradeController::class,['index','update','create','destroy']); //等级
//                    phcentAddRouter('/message',Controllers\Admin\V1\MessageController::class,['index','update','destroy']); //信息
//                    phcentAddRouter('/notice',Controllers\Admin\V1\NoticeController::class,['index','update','destroy']); //通知
//                    phcentAddRouter('/question',Controllers\Admin\V1\QuestionController::class,['index','update','destroy','recovery']); //问题
//                    phcentAddRouter('/report',Controllers\Admin\V1\ReportController::class,['index','update','destroy']); //举报
//                    phcentAddRouter('/signin',Controllers\Admin\V1\SigninController::class,['index']); //签到
//                    phcentAddRouter('/tags',Controllers\Admin\V1\TagsController::class,['index','create','update','destroy','recovery']); //话题
//                    phcentAddRouter('/user',Controllers\Admin\V1\UserController::class,['index','update']); //会员
//                })->middleware(Middleware\AdminAuthMiddleware::class);
//                // 会员中心路由
//                Route::group('/user', function () {
//                    phcentAddRouter('/cash',Controllers\User\V1\CashController::class,['index','create']); //提现
//                    phcentAddRouter('/recharge',Controllers\User\V1\RechargeController::class,['index','create']); //充值
//                    phcentAddRouter('/report',Controllers\User\V1\ReportController::class,['index','create']); //举报
//                });
//                // 会员中心路由
//                Route::group('/web', function () {
//                    phcentAddRouter('/question',Controllers\Web\V1\QuestionController::class,['index','create','update','destroy','close','open','config']);
//                    phcentAddRouter('/article',Controllers\Web\V1\ArticleController::class,['index','create','update','destroy','config']);
//                    phcentAddRouter('/auth',Controllers\Web\V1\AuthController::class,['login','refresh','me','code','reg','forget','logout']); //登入注册相关
//                });
//
//            });





            Route::group('/{version}/admin', function () {
                Route::any('/{controller}/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\Admin\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    $request->id = $id;
                    return call_user_func([$controller, $action], $request, $id);
                });
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
            })->middleware(Middleware\AdminAuthMiddleware::class);
            Route::group('/{version}/user', function () {
                Route::any('/{controller}/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\User\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    $request->id = $id;
                    return call_user_func([$controller, $action], $request, $id);
                });
            });
            Route::group('/{version}/web', function () {
                Route::any('/{controller}/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\Web\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    $request->id = $id;
                    return call_user_func([$controller, $action], $request, $id);
                });
            });
        });

        Route::options('/api/{all:.*}', function () {
            return response('');
        });
        Route::fallback(function () {
            return phcentJson(config('phcentask.code.intel_bad'), '404 not found');
        });
    }
}