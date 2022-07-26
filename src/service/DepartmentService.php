<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\{Department};
use lgdz\hyperf\Tools;
use lgdz\object\Body;
use lgdz\object\Query;

class DepartmentService
{
    public function index(Query $input)
    {
        $list = Department::query()->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->org_id, function ($query, $value) {
            return $query->where('org_id', $value);
        })->when($input->pid, function ($query, $value) {
            return $query->whereRaw("find_in_set({$value},path)");
        })->orderByRaw('len asc,sort asc,id asc')->get()->each(function (Department $department) {
            $department->append('member_count');
        })->toArray();
        return empty($list) ? [] : Tools::F()->tree->build($list, $list[0]['pid']);
    }

    public function create(Body $input)
    {
        $input->org_id && !Tools::IsTargetParentOrg($input->org_id) && Tools::E('超出可管理部门范围');
        $department = new Department();
        $department->setFormData($input);
        $department->save();
        // 生成path
        $department->savePath();
    }

    public function update(int $id, Body $input)
    {
        $department = $this->department($this->findById($id));

        !Tools::IsTargetParentOrg($department->org_id) && Tools::E('超出可管理部门范围');

        // 修改前的部门pid
        $old_pid = $department->pid;

        $department->setFormData($input);
        // 修改后的部门pid
        $new_pid = $department->pid;

        $department->save();

        // 生成path
        $department->savePath();
        // 更新了pid
        if ((int)$new_pid !== $old_pid) {
            $this->updateChild($department->id);
        }
    }

    // 更新子级path
    private function updateChild(int $id)
    {
        Department::query()->where('pid', $id)->get()->each(function (Department $department) {
            $department->savePath();
            $this->updateChild($department->id);
        });
    }

    public function delete(int $id)
    {
        $department = $this->department($this->findById($id));
        !Tools::IsTargetParentOrg($department->org_id) && Tools::E('超出可管理部门范围');
        $department->delete();
    }

    public function findById(int $id)
    {
        return Department::findFromCache($id);
    }

    /**
     * 验证参数是否是Department对象，如不是抛出异常
     * @param $department
     * @return Department
     */
    public function department($department): Department
    {
        return ($department instanceof Department) ? $department : Tools::E('部门不存在');
    }

    public function select(int $org_id)
    {
        if (!$org_id) {
            $org_id = Tools::Org()->id;
        }
        $org = Tools::OrgById($org_id);
        $result = ['id' => 0, 'code' => '0', 'name' => $org->name];
        $list = Department::query()->where('org_id', $org_id)->orderByRaw('len asc,sort asc,id asc')->get()->toArray();
        if (!empty($list)) {
            $result['children'] = Tools::F()->tree->build($list, $list[0]['pid']);
        }
        return [$result];
    }
}