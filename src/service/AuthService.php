<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\User;

/**
 * Class AuthService 登录权限认证服务（适用于'ant design vue admin'）
 * @package App\Service
 */
class AuthService extends AbstractAuthService
{
    // 获取账户路由权限
    public function getRouterConfig(User $user): array
    {
        $user_id = $user->id;
        $rules = $this->getRoleRules($user);
        $api_list = [];
        $page_list = [];
        foreach ($rules as $rule) {
            $rule['type'] === 'api' && array_push($api_list, $rule);
            $rule['type'] === 'page' && array_push($page_list, $rule);
        }
        $result = [
            'permissions' => $this->clientPermissions($api_list),
            'routes'      => $this->clientRouters($page_list, 0)
        ];

        $this->setPowers($api_list, $user_id);

        return $result;
    }

    // 生成客户端routes
    protected function clientRouters(array $list, int $id)
    {
        $tmp = [];
        foreach ($list as $row) {
            $item = [];
            if ($row['pid'] !== $id) {
                continue;
            }
            $child = $this->clientRouters($list, $row['id']);
            if ($child) {
                $item['router'] = $row['client_router'];
                $item['children'] = $child;
            } else {
                $item = $row['client_router'];
            }
            $tmp[] = $item;
        }
        return $tmp;
    }

    // 生成客户端permissions
    protected function clientPermissions(array $list)
    {
        $tmp = [];
        foreach ($list as $row) {
            $group = $row['pid'];
            if (!isset($tmp[$group]['id'])) {
                $tmp[$group]['id'] = null;
            }
            if (!isset($tmp[$group]['operation'])) {
                $tmp[$group]['operation'] = [];
            }
            if (!isset($tmp[$group]['id']) && !empty($row['permission_id'])) {
                $tmp[$group]['id'] = $row['permission_id'];
            } else {
                $tmp[$group]['operation'][] = $row['operation'];
            }
        }
        return array_values($tmp);
    }

    public function powerKey(): string
    {
        return config('lgdz.power.ticket_key', 'power_ticket');
    }
}