<?php

declare(strict_types=1);

return [
    // http数据加密
    'encrypt' => [
        'enable' => false,
        'offset' => 0,
        'length' => 6
    ],
    // 后台登录
    'auth' => [
        // jwt加密key
        'secret' => '123456',
        // 存储到redis中的用户登录凭证key
        'ticket_key' => 'user_ticket',
        // 是否是单点登录
        'sso' => false,
        // 是否需要登录日志
        'login_log' => false,
        'lock' => [
            'enable' => false,
            'key' => 'user_lock',
            'bad' => 3, // 密码错误次数
            'time' => 1 // 锁定时长（分钟）
        ],
    ],
    // 权限
    'power' => [
        // 开启|关闭权限验证
        'enable' => false,
        // 存储到redis中的用户权限集key
        'ticket_key' => 'power_ticket',
    ],
    'account' => [
        // 是否支持多账户
        'many' => true,
        // 账户中间件附加处理
        'callback_enable' => false,
        // 中间件处理类
        'callback_class' => '',
        // 中间件处理类中的方法
        'callback_method' => 'middleware',
        // 免处理路由地址
        'callback_free_router' => ['/l/perfect_info']
    ],
    'wechat' => [
        // 参考easywechat配置
        'work' => [
            'corp_id' => '', // 企业ID
            'secret' => ''
        ]
    ],
    // 自定义组件接口类
    'component_api' => null,
    // 完善信息
    'must_perfect_info' => [
        'enable' => false,
        // 必须完善信息字段
        'fields' => ['realname', 'phone'],
        // 强制修改密码
        'password' => true
    ],
];