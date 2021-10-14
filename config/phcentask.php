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
        'user' => Phcent\WebmanAsk\Model\User::class,
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

    'balanceOperation' =>[
        'addReward'=>'追加悬赏',
        'bestAnswer'=>'最佳答案分成',
        'inviteAsk'=>'邀请问答',
        'postReward'=>'发布悬赏问题',
        'payInvite'=>'付费邀请',
        'backReward'=>'悬赏退回',
        'payArticle'=>'付费查看文章',
        'payAnswer'=>'付费查看答案'
    ], //余额变动类型
    'pointsOperation' => [
        'signin'=>'签到',
        'postReward'=>'发布悬赏问题',
        'addReward'=>'追加悬赏',
        'bestAnswer'=>'最佳答案分成',
        'payArticle'=>'付费查看文章',
        'payAnswer'=>'付费查看答案',
        'deleteAnswer'=>'删除回答',
        'publishAnswer'=>'发布回答',
        'deleteReply'=>'删除回复/评论',
        'publishReply'=>'发表回复/评论',
    ], //积分变动类型
    'orderOperation' => [
        'recharge' => '账户充值', // 账户充值
        'thankArticle' => '感谢文章', // 打赏
        'thankQuestion' => '感谢问题', // 付费查看文章
        'thankAnswer' => '感谢回答',
        'payArticle' => '付费文章', // 打赏
    ],
    'reasonList' => [1=>'色情低俗',2=>'政治敏感',3=>'违法暴力',4=>'恐怖血腥',5=>'非法贩卖',6=>'仇恨言论',7=>'打小广告',8=>'其他'], //举报原因
    'reportType' => [1=>'问题',2=>'文章',3=>'回答',4=>'评论',5=>'标签'], //举报类型
    'replyType' => [1=>'回答',2=>'文章'], //回复类型
    'collectionType' => [ 1 =>'问题',3=>'回答',2=>'文章'], //收藏类型
    'categoryType' => [ 1 => '问题', 2 => '文章', 3 => '标签', 4 => '专家'],
    'dynamicType' => [ 1 => '问题', 2 => '文章', 3 => '回答', 4 => '评论'],
    'dynamicStage' => [ 'create' => '发布了', 'update' => '补充了', 'collection' => '收藏了', 'follow' => '关注了'],
    'diggType' => [ 1 => '问题', 2 => '文章', 3 => '回答', 4 => '评论'],
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
    ]
];