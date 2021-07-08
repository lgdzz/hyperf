<?php

declare(strict_types=1);

namespace lgdz\hyperf\validator;

use lgdz\hyperf\Tools;

class AuthValidator implements ValidatorInterface
{
    public function rule(): array
    {
        return [];
    }

    public function message(): array
    {
        return [];
    }

    public function custom(array $data, string $scene): void
    {
        switch ($scene) {
            case 'login':
                if (!isset($data['username']) || !$data['username']) {
                    Tools::E('请输入用户名');
                } elseif (!isset($data['password']) || !$data['password']) {
                    Tools::E('请输入密码');
                }
                break;
            case 'changePwd':
                if (!isset($data['old_password']) || !$data['old_password']) {
                    Tools::E('请输入旧密码');
                } elseif (!isset($data['password']) || !$data['password']) {
                    Tools::E('请输入新密码');
                }
                break;
        }
    }
}
