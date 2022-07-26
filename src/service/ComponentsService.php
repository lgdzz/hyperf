<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Di\Annotation\Inject;
use lgdz\hyperf\Tools;
use lgdz\object\Query;

class ComponentsService
{
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

    // 当前时间
    public function NowTime()
    {
        return Tools::F()->time->nowTime();
    }

    // 最近登录列表
    public function LoginLog(int $limit = 10)
    {
        return $this->LoginLogService->index(new Query([
            'user_id' => Tools::U()->id,
            'limit'   => $limit
        ]));
    }

    // 数据字典
    public function Dictionary(...$names)
    {
        $list = [];
        foreach ($names as $name) {
            $list[$name] = Tools::D2Tree($name);
        }
        return $list;
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

    // 部门列表
    public function DepartmentTree(int $org_id = 0)
    {
        return Tools::container()->get(DepartmentService::class)->select($org_id);
    }

    // 角色列表
    public function RoleTree(int $org_id = 0)
    {
        return Tools::container()->get(RoleService::class)->select($org_id);
    }

    // 组织用户列表(account)
    public function OrgUser(int $org_id = 0)
    {
        return Tools::container()->get(AccountService::class)->select($org_id ?: Tools::Org()->id);
    }

    // 部门用户列表(account)
    public function DepartmentUser(int $org_id = 0, int $department_id = 0)
    {
        if (!$org_id && !$department_id) {
            return Tools::container()->get(DepartmentMemberService::class)->myDeptMembers();
        } else {
            return Tools::container()->get(DepartmentMemberService::class)->members($org_id, $department_id);
        }
    }
    
    public function PingUrl(string $method, string $url)
    {
        return Tools::F()->request->$method($url);
    }
}