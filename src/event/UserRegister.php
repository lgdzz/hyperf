<?php

declare(strict_types=1);

namespace lgdz\hyperf\event;

// 账号注册事件
class UserRegister
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}