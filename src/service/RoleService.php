<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\Factory;
use lgdz\hyperf\model\{Role, Rule};
use lgdz\hyperf\Tools;

class RoleService
{
    public function index(array $input)
    {
        $status = $input['status'] ?? false;
        $pid = $input['pid'] ?? false;

        $list = Role::query()->when($status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($pid, function ($query, $value) {
            return $query->whereRaw("find_in_set({$value},path)");
        })->orderByRaw('pid asc,id asc')->get()->toArray();

        return empty($list) ? Factory::container()->tree->build($list, $list[0]['pid']) : [];
    }

    public function create(array $input)
    {
        // 比对编辑者权限，如果超出则创建失败
        $this->compareEditorRules($input['pid'], $input['rules']);
        $role = new Role();
        $role->setFormData($input);
        $role->save();
        // 生成path
        $path = Role::query()->where('id', $role->pid)->value('path');;
        $role->path = $path . ',' . $role->id;
        $role->save();
    }

    public function update(int $id, array $input)
    {
        $role = Role::query()->where('id', $id)->firstOrFail();
        $role->setFormData($input, true);
        $role->save();
    }

    public function delete(int $id)
    {
        $role = $this->role($this->findById($id));

        $role->delete();
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
}