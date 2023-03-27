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
     * @var Service
     */
    private $service;

    // 当前时间
    public function NowTime()
    {
        return $this->service->factory->time->nowTime();
    }

    // 最近登录列表
    public function LoginLog(int $limit = 10)
    {
        return $this->service->loginLog->index(new Query([
            'user_id' => Tools::U()->id,
            'limit' => $limit
        ]));
    }

    // 数据字典
    public function Dictionary(...$names)
    {
        if (empty($names)) {
            return Tools::D2TreeAll();
        } else {
            $list = [];
            foreach ($names as $name) {
                $list[$name] = Tools::D2Tree($name);
            }
            return $list;
        }
    }

    // 权限列表
    public function RoleRuleTree(int $role_id)
    {
        $rule_list = $this->service->auth->getRoleRules(
            $this->service->role->role($this->service->role->findById($role_id))
        );
        return empty($rule_list) ? [] : Tools::F()->tree->build($rule_list, $rule_list[0]['pid']);
    }

    // 组织类型
    public function OrgGradeTree(int $grade_id = 0, bool $self = false)
    {
        return $this->service->organizationGrade->select($grade_id, $self);
    }

    // 组织列表
    public function OrgTree(int $org_id = 0, bool $self = true)
    {
        return $this->service->organization->select($org_id, $self);
    }

    // 部门列表
    public function DepartmentTree(int $org_id = 0)
    {
        return $this->service->department->select($org_id);
    }

    // 角色列表
    public function RoleTree(int $org_id = 0)
    {
        return $this->service->role->select($org_id);
    }

    // 组织用户列表(account)
    public function OrgUser(int $org_id = 0)
    {
        return $this->service->account->select($org_id ?: Tools::Org()->id);
    }

    // 部门用户列表(account)
    public function DepartmentUser(int $org_id = 0, int $department_id = 0)
    {
        if (!$org_id && !$department_id) {
            return $this->service->departmentMember->myDeptMembers();
        } else {
            return $this->service->departmentMember->members($org_id, $department_id);
        }
    }

    public function PingUrl(string $method, string $url)
    {
        return $this->service->factory->request->$method($url);
    }
}