<?php

declare(strict_types=1);

return [
    // 后台登录
    'auth'   => [
        // jwt加密key
        'secret'     => '123456',
        // 存储到redis中的用户登录凭证key
        'ticket_key' => 'user_ticket',
        // 是否是单点登录
        'sso'        => false
    ],
    // 权限
    'power'  => [
        // 开启|关闭权限验证
        'enable'     => false,
        // 存储到redis中的用户权限集key
        'ticket_key' => 'power_ticket',
    ],
    'wechat' => [
        // 参考easywechat配置
        'work' => [
            'corp_id'  => '', // 企业ID
            'secret'   => ''
        ]
    ]
];