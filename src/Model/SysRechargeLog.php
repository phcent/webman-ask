<?php
/**
 *-------------------------------------------------------------------------p*
 * 余额变动日志
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



use Phcent\WebmanAsk\Logic\PriceLogic;

class SysRechargeLog extends Model
{

    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'sys_recharge_log';

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
        'amount' => 0,
        'give_amount' => 0,
        'orders_id' => 0,
        'status' => 0
    ];

    /**
     * 不可批量赋值的属性
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'amount' => PriceLogic::class,
        'give_amount' => PriceLogic::class,
    ];
    protected $dates = [
        'pay_time'
    ];

    public function orders()
    {
        return $this->hasOne(SysOrders::class,'id','orders_id');
    }

}
