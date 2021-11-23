<?php
/**
 *-------------------------------------------------------------------------p*
 * 会员数据模型
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
namespace Phcent\WebmanAsk\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Avatar\Avatar;
use Phcent\WebmanAsk\Logic\PriceLogic;

class SysUser extends Model
{
    use SoftDeletes;
    protected $table = 'sys_users';

    protected $attributes=[
        'login_num' => 0,
        'status' => 1
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = [
//        'name', 'email','mobile',
//    ];
    /**
     * 不可批量赋值的属性
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'available_balance' => PriceLogic::class,
        'freeze_balance' => PriceLogic::class,
        'available_points' => PriceLogic::class,
        'freeze_points' => PriceLogic::class,
        'points' => PriceLogic::class,

    ];

    protected $appends= ['avatar_url'];

    public function getAvatarUrlAttribute($key)
    {
        return $this->avatar ? phcentFileUrl($this->avatar) :(new Avatar(config('phcentask.avatar')))->create($this->nick_name)->toBase64();
    }
}
