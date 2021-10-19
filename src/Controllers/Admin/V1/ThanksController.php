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


use Phcent\WebmanAsk\Logic\AdminControllerLogic;

class ThanksController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskThanks::class;
    public  $name = '感谢';
    public  $projectName = '问答管理-感谢管理-';
}