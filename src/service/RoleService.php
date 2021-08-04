<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\object\Body;
use lgdz\object\Query;
use lgdz\hyperf\model\{OrganizationGrade, Role, Rule};
use lgdz\hyperf\Tools;

class RoleService
{
    public function index(Query $input)
    {
        $list = Role::query()->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->pid, function ($query, $value) {
            return $query->whereRaw("find_in_set({$value},path)");
        })->when($input->org_id, function ($query, $value) {
            return $query->where('org_id', $value);
        })->orderByRaw('pid asc,id asc')->get()->toArray();

        return empty($list) ? [] : Tools::F()->tree->build($list, $list[0]['pid']);
    }

    public function create(Body $input)
    {
        if ($input->org_id && !Tools::IsTargetParentOrg($input->org_id)) {
            Tools::E('超出可管理组织范围');
        } else {
            $input->org_id = Tools::Org()->id;
        }
        // 比对编辑者权限，如果超出则创建失败
        $this->compareEditorRules($input->pid, $input->rules);
        $role = new Role();
        $role->setFormData($input);
        $role->save();
        // 生成path
        $path = Role::query()->where('id', $role->pid)->value('path');;
        $role->path = $path . ',' . $role->id;
        $role->save();
    }

    public function update(int $id, Body $input)
    {
        $role = $this->role($this->findById($id));
        // 比对编辑者权限，如果超出则创建失败
        $this->compareEditorRules($role->pid, $input->rules);
        $role->setFormData($input, true);
        $role->save();
    }

    public function delete(int $id)
    {
        $role = $this->role($this->findById($id));
        count($role->children) > 0 && Tools::E('请先删除子角色');
        try {
            $role->delete();
        } catch (\Exception $e) {
            Tools::E('删除失败');
        }
    }

    /**
     * 比对编辑者权限
     * @param int $parent_role_id
     * @param array $rules
     */
    protected function compareEditorRules(int $parent_role_id, array $rules)
    {
        $parent_role = Role::query()->where('id', $parent_role_id)->firstOrFail();
        // 超级角色不比对权限集
        if ($parent_role->master) {
            return;
        }
        // 超出权限集ID
        $beyond = array_diff($rules, Rule::fullRulesIds($parent_role->rules));
        !empty($beyond) && Tools::E('超出您自身权限范围，超出值[' . implode(',', $beyond) . ']');
    }

    public function findById(int $id)
    {
        return Role::query()->where('id', $id)->first();
    }

    /**
     * 验证参数是否是Role对象，如不是抛出异常
     * @param $role
     * @return Role
     */
    public function role($role): Role
    {
        return ($role instanceof Role) ? $role : Tools::E('角色不存在');
    }

    public function select(int $org_id = 0)
    {
        $fields = ['id', 'pid', 'org_id', 'path', 'name'];
        if (!$org_id || $org_id === Tools::Org()->id) {
            $org_id = Tools::Org()->id;
            // 以自身角色为根返回下级角色
            $role_id = Tools::Account()->role_id;
            $list = Role::query()->whereRaw("find_in_set({$role_id},path)")->orderBy('id')->get($fields)->toArray();
        } else {
            !Tools::IsTargetParentOrg($org_id) && Tools::E('超出您自身管理组织范围');
            $org_service = Tools::container()->get(OrganizationService::class);
            $org = $org_service->org($org_service->findById($org_id));
            $admin_role_id = (int)OrganizationGrade::query()->where('id', $org->grade_id)->value('admin_role_id');
            // 以组织为根返回下级角色
            $list = Role::query()->where('id', $admin_role_id)->orWhere('org_id', $org_id)->orderBy('id')->get($fields)->toArray();
        }
        return empty($list) ? [] : Tools::F()->tree->build($list, $list[0]['pid']);
    }
}