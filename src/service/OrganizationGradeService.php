<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\object\Body;
use lgdz\object\Query;
use lgdz\hyperf\model\OrganizationGrade;
use lgdz\hyperf\Tools;

class OrganizationGradeService
{
    public function select(int $grade_id = 0, bool $self = false)
    {
        if ($grade_id) {
            $model = OrganizationGrade::query()->whereRaw("find_in_set({$grade_id},path)");
        } else {
            $model = OrganizationGrade::query();
        }
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