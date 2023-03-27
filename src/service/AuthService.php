<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Account;
use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;

/**
 * Class AuthService 登录权限认证服务（适用于'ant design vue admin'）
 * @package App\Service
 */
class AuthService extends AbstractAuthService
{
    // 获取账户路由权限
    public function getRouterConfig(int $account_id): array
    {
        $role = $this->getRoleByAccountId($account_id);
        $rules = $this->getRoleRules($role, 'login');
        $api_list = [];
        $page_list = [];
        foreach ($rules as $rule) {
            $rule['type'] === 'api' && array_push($api_list, $rule);
            $rule['type'] === 'page' && array_push($page_list, $rule);
        }
        // 过滤虚拟页面得到真实页面列表
        $page_list2 = [];
        foreach ($page_list as $index => $item) {
            if (!Tools::Service()->factory->helper->contains($item['client_router'], 'XNYM-')) {
                $page_list2[] = $item;
            }
        }
        $result = [
            'permissions' => $this->clientPermissions($page_list, $api_list),
            'routes' => $this->clientRouters($page_list2, 0)
        ];

        $this->setPowers($rules, $account_id);

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
    protected function clientPermissions(array $page_list, array $list)
    {
        $permission_ids = array_column($page_list, 'client_router', 'id');
        $tmp = [];
        // 处理页面自带接口权限
        foreach ($page_list as $page) {
            if ($page['service_router']) {
                $tmp[$page['id']] = [
                    'id' => $page['client_router'],
                    'operation' => []
                ];
            }
        }
        // 处理接口权限
        foreach ($list as $row) {
            if (!$row['operation']) {
                continue;
            }
            $group = $row['pid'];
            if (!isset($tmp[$group]['id'])) {
                $tmp[$group]['id'] = null;
            }
            if (!isset($tmp[$group]['operation'])) {
                $tmp[$group]['operation'] = [];
            }
            if (!isset($tmp[$group]['id']) && isset($permission_ids[$row['pid']])) {
                $tmp[$group]['id'] = $permission_ids[$row['pid']];
            }
            $tmp[$group]['operation'][] = $row['operation'];
        }
        return array_values($tmp);
    }

    public function powerKey(): string
    {
        return config('lgdz.power.ticket_key', 'power_ticket');
    }
}