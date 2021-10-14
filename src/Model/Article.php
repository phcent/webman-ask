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


namespace Phcent\WebmanAsk\Model;

//use Illuminate\Database\Eloquent\Model; //不开启缓存则去掉注释

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{

    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'article';

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
        'type' => 1,
        'group' => 1,
        'status' => 0,
        'view' => 0,
        'likes' => 0,
        'comment' => 0,
        'sort' => 0,
    ];

    /**
     * 不可批量赋值的属性
     *
     * @var array
     */
    protected $guarded = [];

    protected $appends = ['image_url','year','mouth'];

    public function getImageUrlAttribute($key)
    {
        return $this->image_name ? (preg_match('/^http(s)?:\\/\\/.+/',$this->image_name)?$this->image_name:Storage::url($this->image_name)) :'';
    }

    public function cate()
    {
        return $this->hasOne(ArticleCate::class,'id','cate_id');
    }
    public function getYearAttribute($key)
    {
        return Date::parse($this->created_at)->format('Y');
    }
    public function getMouthAttribute($key)
    {
        return Date::parse($this->created_at)->format('m-d');
    }
}
