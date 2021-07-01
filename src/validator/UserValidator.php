<?php

declare(strict_types=1);

namespace lgdz\hyperf\validator;

class UserValidator implements ValidatorInterface
{
    public function rule(): array
    {
        return [
            'username' => 'required',
            'password' => 'required',
        ];
    }

    public function message(): array
    {
        return [
            'username.required' => '用户名未定义',
            'password.required' => '密码未定义',
        ];
    }

    public function custom(array $data, string $scene): void
    {
    }
}
