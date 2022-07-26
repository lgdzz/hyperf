<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Account;
use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;

/**
 * Class AuthService 登录权限认证服务（适用于'wuzhile'）
 * @package App\Service
 */
class AuthService2 extends AbstractAuthService
{
    function getRouterConfig(int $account_id): array
    {
        $role = $this->getRoleByAccountId($account_id);
        $rules = $this->getRoleRules($role, 'login');
        $operation = [];
        $api_list = [];
        $page_list = [];
        foreach ($rules as $rule) {
            if ($rule['type'] === 'api') {
                if ($rule['operation']) {
                    $operation[$rule['pid']][] = $rule['operation'];
                }
                array_push($api_list, $rule);
            }
            $rule['type'] === 'page' && array_push($page_list, $rule);
        }
        $list = [];
        foreach ($page_list as $route) {
            $list[] = [
                'id'   => $route['id'],
                'pid'  => $route['pid'],
                'path' => $route['client_router'],
                'name' => $route['client_route_name'],
                'meta' => [
                    'title' => $route['name'],
                    'icon'  => $route['icon'],
                    'auth'  => isset($operation[$route['id']]) ? $operation[$route['id']] : []
                ]
            ];
        }
        $result = empty($list) ? [] : Tools::F()->tree->build($list, $list[0]['pid']);
        $this->setPowers($rules, $account_id);
        return $result;
    }

    public function powerKey(): string
    {
        return config('lgdz.power.ticket_key', 'power_ticket');
    }
}