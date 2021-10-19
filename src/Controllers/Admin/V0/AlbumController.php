<?php
/**
 *-------------------------------------------------------------------------p*
 * 附件管理
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

namespace Phcent\WebmanAsk\Controllers\Admin\V0;

use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Model\SysAlbumFiles;
use support\Request;

class AlbumController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\SysAlbumFiles::class;
    public  $name = '附件列表';
    public  $projectName = '系统管理-附件管理-';
}