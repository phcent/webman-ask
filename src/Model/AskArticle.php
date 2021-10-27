<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答文章数据模型
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

//use Illuminate\Database\Eloquent\Model; //不开启缓存则去掉注释

use Illuminate\Database\Eloquent\SoftDeletes;
use Phcent\WebmanAsk\Logic\PriceLogic;

class AskArticle extends Model
{
    use SoftDeletes;
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'ask_article';

    /**
     * 与表关联的主键
     *
     * @var string
     */
    // protected $primaryKey = 'flight_id';

    /**
     * 主键是否主动递增
     *
     * @var bool
     */
    // public $incrementing = false;

    /**
     * 自动递增主键的「类型」
     *
     * @var string
     */
    // protected $keyType = 'string';

    /**
     * 是否主动维护时间戳
     *
     * @var bool
     */
    // public $timestamps = false;

    /**
     * 模型日期的存储格式
     *
     * @var string
     */
    // protected $dateFormat = 'U';

    /**
     * 模型的数据库连接名
     *
     * @var string
     */
    // protected $connection = 'connection-name';

    /**
     * 可批量赋值属性
     *
     * @var array
     */
    // protected $fillable = [];

    /**
     * 模型属性的默认值
     *
     * @var array
     */
    protected $attributes = [
        'digg_num' => 0,
        'step_num' => 0,
        'view_num' => 0,
        'report_num' => 0,
        'collection_num' => 0,
        'thank_num' => 0,
        'reply_num' => 0,
        'pay_num' => 0,
        'reward_balance' => 0,
        'reward_points' => 0,
        'status' => 1,
        'hot_sort' => 0,
        'top_sort' => 0,

    ];

    /**
     * 不可批量赋值的属性
     *
     * @var array
     */
    protected $guarded = [];
    protected $casts = [
        'reward_balance' => PriceLogic::class,
        'reward_points' => PriceLogic::class,
        'style' => 'json'
    ];

    public function tags()
    {
        return $this->belongsToMany(AskTags::class,'ask_tags_qa','article_id','tag_id');
    }

    public function user()
    {
        return $this->hasOne(SysUser::class,'id','user_id');
    }
}
