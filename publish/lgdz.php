<?php

declare(strict_types=1);

return [
    // 后台登录
    'auth' => [
        // jwt加密key
        'secret'           => '123456',
        // 存储到redis中的用户登录凭证key
        'user_ticket_key'  => 'user_ticket',
        // 存储到redis中的用户权限集key
        'power_ticket_key' => 'power_ticket',
        // 是否是单点登录
        'sso'              => false
    ]
];