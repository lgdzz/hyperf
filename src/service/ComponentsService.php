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
    public function RoleRuleTree(int $role_id)
    {
        $rule_list = $this->AuthService->getRoleRules(
            $this->RoleService->role($this->RoleService->findById($role_id))
        );
        return empty($rule_list) ? [] : Tools::F()->tree->build($rule_list, $rule_list[0]['pid']);
    }

    // 组织类型
    public function OrgGradeTree(int $grade_id = 0, bool $self = false)
    {
        return Tools::container()->get(OrganizationGradeService::class)->select($grade_id, $self);
    }

    // 组织列表
    public function OrgTree(int $org_id = 0, bool $self = true)
    {
        return Tools::container()->get(OrganizationService::class)->select($org_id, $self);
    }

    // 角色列表
    public function RoleTree(int $org_id = 0)
    {
        return Tools::container()->get(RoleService::class)->select($org_id);
    }
}