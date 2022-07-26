<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Organization;
use lgdz\object\Body;
use lgdz\object\Query;
use lgdz\hyperf\model\OrganizationGrade;
use lgdz\hyperf\Tools;

class OrganizationGradeService
{
    public function index(Query $input)
    {
        $list = OrganizationGrade::query()->with('role')->orderByRaw('len asc,sort asc,id asc')->get()->toArray();
        return empty($list) ? [] : Tools::F()->tree->build($list, $list[0]['pid']);
    }

    public function create(Body $input)
    {
        $grade = new OrganizationGrade();
        $grade->setFormData($input);
        $grade->save();
        // 生成path
        $grade->savePath();
    }

    public function update(int $id, Body $input)
    {
        $grade = $this->grade($this->findById($id));

        $old_pid = $grade->pid;

        $grade->setFormData($input);
        $grade->save();

        $new_pid = $grade->pid;

        // 生成path
        $grade->savePath();
        // 更新了pid
        if ((int)$new_pid !== $old_pid) {
            $this->updateChild($grade->id);
        }
    }

    // 更新子级path
    private function updateChild(int $id)
    {
        OrganizationGrade::query()->where('pid', $id)->get()->each(function (OrganizationGrade $grade) {
            $grade->savePath();
            $this->updateChild($grade->id);
        });
    }

    public function delete(int $id)
    {
        $grade = $this->grade($this->findById($id));
        if (Organization::query()->where('grade_id', $grade->id)->exists()) {
            Tools::E('类型正在被组织所使用，禁止删除');
        } elseif (OrganizationGrade::query()->where('pid', $grade->id)->exists()) {
            Tools::E('有子级禁止删除类型');
        }
        $grade->delete();
    }

    public function findById(int $id)
    {
        return OrganizationGrade::query(true)->where('id', $id)->first();
    }

    /**
     * 验证参数是否是OrganizationGrade对象，如不是抛出异常
     * @param $grade
     * @return OrganizationGrade
     */
    public function grade($grade): OrganizationGrade
    {
        return ($grade instanceof OrganizationGrade) ? $grade : Tools::E('组织类型不存在');
    }

    public function select(int $grade_id = 0, bool $self = false)
    {
        if (!$grade_id) {
            $grade_id = Tools::Org()->grade_id;
        }
        $model = OrganizationGrade::query()->whereRaw("find_in_set({$grade_id},path)");
        $list = $model->orderByRaw('len asc,sort asc,id asc')->get()->toArray();
        if (empty($list)) {
            return [];
        } else {
            $tree = Tools::F()->tree->build($list, $list[0]['pid']);
            if (!$self) {
                $tree = $tree[0]['children'] ?? [];
            }
            return $tree;
        }
    }
}