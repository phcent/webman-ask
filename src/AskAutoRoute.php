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


use Webman\Route;

class AskAutoRoute
{
    /**
     * 路由注册
     */
    static function load()
    {
        AskRoute::group('/api', function () {

            collect(glob(app_path().'/phcent/*/routes.php'))->map(function($filename) {
                AskRoute::group('/extend',function () use ($filename) {
                    return include_once $filename;
                });
            });


           AskRoute::group('/v0/admin',  function () {
                AskRoute::resource('/ajax',Controllers\Admin\V0\AjaxController::class,['cache','menu'])->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class);
                AskRoute::resource('/album',Controllers\Admin\V0\AlbumController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/cash',Controllers\Admin\V0\CashController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/log',Controllers\Admin\V0\LogController::class,['index','destroy'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/menu',Controllers\Admin\V0\MenuController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/orders',Controllers\Admin\V0\OrdersController::class,['index','update'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/recharge',Controllers\Admin\V0\RechargeController::class,['index','update'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/team',Controllers\Admin\V0\TeamController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/user',Controllers\Admin\V0\UserController::class,['index','update','create','destroy','recovery','money','points'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
            });

            AskRoute::group('/v1/admin', function () {
                AskRoute::resource('/answer',Controllers\Admin\V1\AnswerController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/article',Controllers\Admin\V1\ArticleController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/category',Controllers\Admin\V1\CategoryController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/dynamic',Controllers\Admin\V1\DynamicController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/grade',Controllers\Admin\V1\GradeController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/message',Controllers\Admin\V1\MessageController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/notice',Controllers\Admin\V1\NoticeController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/orders',Controllers\Admin\V1\OrdersController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/question',Controllers\Admin\V1\QuestionController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/reply',Controllers\Admin\V1\ReplyController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/report',Controllers\Admin\V1\ReportController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/signin',Controllers\Admin\V1\SigninController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/tags',Controllers\Admin\V1\TagsController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/thanks',Controllers\Admin\V1\ThanksController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/user',Controllers\Admin\V1\UserController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
            });

            //会员路由
            AskRoute::group('/{version}/user/{controller}', function () {
                AskRoute::any('/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\User\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    return call_user_func([$controller, $action], $request, $id);
                });
            })->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class);
            //前台路由
            AskRoute::group('/{version}/web/{controller}', function () {
                AskRoute::any('/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\Web\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    $request->page = $request->input('page',1);
                    return call_user_func([$controller, $action], $request, $id);
                });
            });
        });

        AskRoute::options('/api/{all:.*}', function () {
            return response('不要淘气哦！路由不存在啦');
        });
        AskRoute::fallback(function () {
            return phcentJson(config('phcentask.code.intel_bad'), '不要淘气哦！链接不存在啦');
        });
    }
}