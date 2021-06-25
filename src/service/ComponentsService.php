<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Di\Annotation\Inject;
use lgdz\hyperf\Tools;

class ComponentsService
{
    /**
     * @Inject()
     * @var DictionaryService
     */
    protected $DictionaryService;

    /**
     * @Inject()
     * @var AuthService
     */
    protected $AuthService;

    /**
     * @Inject()
     * @var RoleService
     */
    protected $RoleService;

    /**
     * @Inject()
     * @var LoginLogService
     */
    protected $LoginLogService;

    // 最近登录列表
    public function LoginLog(int $limit = 10)
    {
        return $this->LoginLogService->index([
            'user_id' => Tools::U()->id,
            'limit'   => $limit
        ]);
    }

    // 数据字典
    public function Dictionary(string $name)
    {
        return $this->DictionaryService->get($name);
    }

    // 权限列表
    public function RuleAll(int $role_id)
    {
        $rule_list = $this->AuthService->getRoleRules(
            $this->RoleService->read($role_id)
        );
        return empty($rule_list) ? [] : Tools::$factory->Tree()->tree($rule_list, $rule_list[0]['pid']);
    }

    // 角色列表
    public function RoleSelect(bool $self)
    {
        $role_id = $self ? Tools::U()->parent_role_id : Tools::U()->role_id;
        $list = $this->RoleService->index([
            'pid' => $role_id
        ])->toArray();
        return Tools::$factory->Tree()->tree($list, $role_id);
    }
}