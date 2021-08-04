<?php

declare(strict_types=1);

namespace lgdz\hyperf\validator;

class AccountValidator implements ValidatorInterface
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
            case 'create':
                if (!isset($data['username']) || !$data['username']) {
                    Tools::E('username未定义');
                } elseif (!isset($data['role_id']) || !$data['role_id']) {
                    Tools::E('role_id未定义');
                }
                break;
            case 'update':
                if (!isset($data['role_id']) || !$data['role_id']) {
                    Tools::E('role_id未定义');
                }
                break;
        }
    }
}
