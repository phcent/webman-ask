<?php
/**
 *-------------------------------------------------------------------------p*
 * 基础设置
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

use Yansongda\Pay\Pay;

return [
    'code' => [
        'intel_yes'  => 200,
        'intel_no' => 400,
        'intel_no_login' => 401,
        'intel_expire' => 402,
        'intel_authority' => 403,
        'intel_bad' => 404,
    ],
    'jwt' => [
        'iss' => 'phcentAsk',
        'aud' => 'phcentAsk',
        'key' => 'w5LgNx5luRRjmamZFSqz3cPHOp9KuQPExlvgi18DN4SdnSI9obcVEhiZVE0NIIC7',
        'exp' => 604800, # 过期时间
    ],
    'cross' => [
        'origin' => '*',
        'methods' => 'GET,POST,PUT,DELETE,OPTIONS',
        'headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin'
    ],
    'guard' => [
        'user' => Phcent\WebmanAsk\Model\SysUser::class,
    ],
    //积分相关
    'points'=>[
        'publishAnswer' => 1, // 发布回答奖励积分
        'publishReply' => 1, // 回复/评论
        'bestAnswer' => 5, // 采纳答案奖励积分
        'publishQuestion' => 3, // 发布回答奖励积分
        'publishArticle' => 5, // 发布文章奖励积分
    ],

    'balanceCommission' => 0, //资金平台扣点 0为不扣点 0-100之间 如 20 则相当于 20 %
    'pointsCommission' => 0, //积分平台扣点 0为不扣点 0-100之间  如 20 则相当于 20 %
    'cashCommission' => 0, //提现扣点 0为不扣点  0-100之间  如 20 则相当于 20 %

    'rechargeRule' => [   //充值赠送规则 满100送3元 满500送20元 不累计赠送 为空则不启用
        100 => 3,
        200 => 5,
        500 => 20,
        1000 => 50,
        2000 => 110,
    ],

    'balanceOperation' =>[
        'bestAnswer'=>'最佳答案分成',
        'inviteAsk'=>'邀请问答',
        'postReward'=>'发布悬赏问题',
        'payInvite'=>'付费邀请',
        'backReward'=>'悬赏退回',
        'payArticle'=>'付费查看文章',
        'payAnswer'=>'付费查看答案',
        'increaseBalance' => '增加预存款',
        'decreaseBalance' => '减少预存款',
        'freezeBalance' => '冻结预存款',
        'unfreezeBalance' => '解冻预存款',
        'applyCash' => '申请提现',
        'appendReward'=>'追加悬赏',
        'recharge' => '充值',
        'postThank' => '发起感谢',
        'receivedThank' => '收到感谢',
    ], //余额变动类型
    'pointsOperation' => [
        'signin'=>'签到',
        'postReward'=>'发布悬赏问题',
        'appendReward'=>'追加悬赏',
        'bestAnswer'=>'最佳答案分成',
        'payArticle'=>'付费查看文章',
        'payAnswer'=>'付费查看答案',
        'deleteAnswer'=>'删除回答',
        'publishAnswer'=>'发布回答',
        'deleteReply'=>'删除回复/评论',
        'publishReply'=>'发表回复/评论',
        'increasePoints' => '增加积分',
        'decreasePoints' => '减少积分',
        'freezePoints' => '冻结积分',
        'unfreezePoints' => '解冻积分',
    ], //积分变动类型
    'orderOperation' => [
        'recharge' => '账户充值', // 账户充值
        'thankArticle' => '感谢文章', // 打赏
        'thankQuestion' => '感谢问题', // 付费查看文章
        'thankAnswer' => '感谢回答',
        'payArticle' => '付费文章', // 打赏
    ],
    'reasonList' => [ 1 =>'色情低俗',2 => '政治敏感', 3 => '违法暴力',4 => '恐怖血腥',5 => '非法贩卖', 6 => '仇恨言论', 7 => '打小广告',8 => '其他'], //举报原因
    'allType' => [1 => '问题', 2 => '文章',3 => '回答',4 => '评论',5 =>'话题', 6 => '专家',7 => '会员'], //站点内置类型 只能增加请勿减少
    'dynamicStage' => [ 'create' => '发布了', 'update' => '补充了', 'collection' => '收藏了', 'follow' => '关注了'],
    'recommendBalance' => [10,20,30,50],
    'recommendPoints' => [5,10,20,30],
    'addBalance' => [1,5,10,20,50],
    'addPoints' => [1,5,10,20,50],
    'signinRule' => [ 31 => 2, 1 => 1 ], //签到奖励积分 1必填不可删除 n为设定最大天数后所得
    'minCash' => 50, //最低可提现金额
    'cashStatusText' => [1 => '提现成功',2 => '提现失败', 10 => '等待审核'],
    'rewardTime' => 7, //悬赏有效期
    'validPost' => [
        'question' => 2, //发布问题是否需要审核 1是 2否
        'article' => 2, //发布文章是否需要审核 1是 2否
        'answer' => 2, //发布回答是否需要审核 1是 2否
        'reply' => 2, //发布评论是否需要审核 1是 2否
    ],
    'email' => [
        'dsn' => 'smtp://username:password@smtp.qq.com:465',
        'encryption' => 'ssl',
        'form_address' => '',
        'from_name' => '象讯问答系统',
    ],
    'sms' => [
        // HTTP 请求的超时时间（秒）
        'timeout' => 5.0,
        // 默认发送配置
        'default' => [
            // 网关调用策略，默认：顺序调用
            'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
            // 默认可用的发送网关
            'gateways' => [
                'yunpian'
            ],
        ],
        // 可用的网关配置
        'gateways' => [
            'errorlog' => [
                'file' =>  runtime_path().'/logs/easy-sms.log',
            ],
            'yunpian' => [
                'api_key' => env('SMS_API_KEY',''),
                'signature' => env('SMS_SIGNATURE','【象讯科技】'), // 内容中无签名时使用
            ],
            //...
        ],
    ],
    'pay' => [
        'alipay' => [
            'default' => [
                // 必填-支付宝分配的 app_id
                'app_id' => '',
                // 必填-应用私钥 字符串或路径
                'app_secret_cert' => '',
                // 必填-应用公钥证书 路径
                'app_public_cert_path' => runtime_path().'/pay/cert/appCertPublicKey.crt',
                // 必填-支付宝公钥证书 路径
                'alipay_public_cert_path' => runtime_path().'/pay/cert/alipayCertPublicKey_RSA2.crt',
                // 必填-支付宝根证书 路径
                'alipay_root_cert_path' => runtime_path().'/pay/cert/alipayRootCert.crt',
                'return_url' => 'https://siteUrl/api/v1/web/alipay/return',
                'notify_url' => 'https://siteUrl/api/v1/web/alipay/notify',
                // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
                'service_provider_id' => '',
                // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
                'mode' => Pay::MODE_NORMAL,
            ]
        ],
        'wechat' => [
            'default' => [
                // 必填-商户号，服务商模式下为服务商商户号
                'mch_id' => '',
                // 必填-商户秘钥
                'mch_secret_key' => '',
                // 必填-商户私钥 字符串或路径
                'mch_secret_cert' => '',
                // 必填-商户公钥证书路径
                'mch_public_cert_path' => '',
                // 必填
                'notify_url' => 'https://siteUrl/api/v1/web/wechat/notify',
                // 选填-公众号 的 app_id
                'mp_app_id' => '',
                // 选填-小程序 的 app_id
                'mini_app_id' => '',
                // 选填-app 的 app_id
                'app_id' => '',
                // 选填-合单 app_id
                'combine_app_id' => '',
                // 选填-合单商户号
                'combine_mch_id' => '',
                // 选填-服务商模式下，子公众号 的 app_id
                'sub_mp_app_id' => '',
                // 选填-服务商模式下，子 app 的 app_id
                'sub_app_id' => '',
                // 选填-服务商模式下，子小程序 的 app_id
                'sub_mini_app_id' => '',
                // 选填-服务商模式下，子商户id
                'sub_mch_id' => '',
                // 选填-微信公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
                'wechat_public_cert_path' => [
                  //  '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                ],
                // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
                'mode' => Pay::MODE_NORMAL,
            ]
        ],
        'logger' => [
            'enable' => false,
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
    ],
    'avatar' => [

        /*
        |--------------------------------------------------------------------------
        | Image Driver
        |--------------------------------------------------------------------------
        | Avatar use Intervention Image library to process image.
        | Meanwhile, Intervention Image supports "GD Library" and "Imagick" to process images
        | internally. You may choose one of them according to your PHP
        | configuration. By default PHP's "Imagick" implementation is used.
        |
        | Supported: "gd", "imagick"
        |
        */
        'driver' => env('IMAGE_DRIVER', 'gd'),

        // Initial generator class
        'generator' => \Laravolt\Avatar\Generator\DefaultGenerator::class,

        // Whether all characters supplied must be replaced with their closest ASCII counterparts
        'ascii' => false,

        // Image shape: circle or square
        'shape' => 'circle',

        // Image width, in pixel
        'width' => 100,

        // Image height, in pixel
        'height' => 100,

        // Number of characters used as initials. If name consists of single word, the first N character will be used
        'chars' => 1,

        // font size
        'fontSize' => 48,

        // convert initial letter in uppercase
        'uppercase' => false,

        // Right to Left (RTL)
        'rtl' => false,

        // Fonts used to render text.
        // If contains more than one fonts, randomly selected based on name supplied
        'fonts' => [__DIR__ . '/../fonts/OpenSans-Bold.ttf', __DIR__ . '/../fonts/rockwell.ttf'],

        // List of foreground colors to be used, randomly selected based on name supplied
        'foregrounds' => [
            '#FFFFFF',
        ],

        // List of background colors to be used, randomly selected based on name supplied
        'backgrounds' => [
            '#f44336',
            '#E91E63',
            '#9C27B0',
            '#673AB7',
            '#3F51B5',
            '#2196F3',
            '#03A9F4',
            '#00BCD4',
            '#009688',
            '#4CAF50',
            '#8BC34A',
            '#CDDC39',
            '#FFC107',
            '#FF9800',
            '#FF5722',
        ],

        'border' => [
            'size' => 1,

            // border color, available value are:
            // 'foreground' (same as foreground color)
            // 'background' (same as background color)
            // or any valid hex ('#aabbcc')
            'color' => 'background',

            // border radius, currently only work for SVG
            'radius' => 0,
        ],

        // List of theme name to be used when rendering avatar
        // Possible values are:
        // 1. Theme name as string: 'colorful'
        // 2. Or array of string name: ['grayscale-light', 'grayscale-dark']
        // 3. Or wildcard "*" to use all defined themes
        'theme' => ['colorful'],

        // Predefined themes
        // Available theme attributes are:
        // shape, chars, backgrounds, foregrounds, fonts, fontSize, width, height, ascii, uppercase, and border.
        'themes' => [
            'grayscale-light' => [
                'backgrounds' => ['#edf2f7', '#e2e8f0', '#cbd5e0'],
                'foregrounds' => ['#a0aec0'],
            ],
            'grayscale-dark' => [
                'backgrounds' => ['#2d3748', '#4a5568', '#718096'],
                'foregrounds' => ['#e2e8f0'],
            ],
            'colorful' => [
                'backgrounds' => [
                    '#f44336',
                    '#E91E63',
                    '#9C27B0',
                    '#673AB7',
                    '#3F51B5',
                    '#2196F3',
                    '#03A9F4',
                    '#00BCD4',
                    '#009688',
                    '#4CAF50',
                    '#8BC34A',
                    '#CDDC39',
                    '#FFC107',
                    '#FF9800',
                    '#FF5722',
                ],
                'foregrounds' => ['#FFFFFF'],
            ],
            'pastel' => [
                'backgrounds' => [
                    '#ef9a9a',
                    '#F48FB1',
                    '#CE93D8',
                    '#B39DDB',
                    '#9FA8DA',
                    '#90CAF9',
                    '#81D4FA',
                    '#80DEEA',
                    '#80CBC4',
                    '#A5D6A7',
                    '#E6EE9C',
                    '#FFAB91',
                    '#FFCCBC',
                    '#D7CCC8',
                ],
                'foregrounds' => [
                    '#FFF',
                ],
            ],
        ]
    ]
];