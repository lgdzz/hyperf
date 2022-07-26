<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\{Account, Department, DepartmentMember};
use lgdz\hyperf\Tools;
use lgdz\object\Body;
use lgdz\object\Query;

class DepartmentMemberService
{
    // 更新部门成员
    public function updateMember(Body $input)
    {
        $org_id = $input->org_id;
        $department_id = $input->department_id;
        $members = $input->members;
        // 清空旧部门成员数据
        DepartmentMember::query()->where('org_id', $org_id)->where('department_id', $department_id)->delete();
        // 添加新部门成员数据
        foreach ($members as $row) {
            $member = new DepartmentMember();
            $member->department_id = $department_id;
            $member->account_id = $row['account_id'];
            $member->is_leader = $row['is_leader'];
            $member->org_id = $org_id;
            $member->save();
        }
    }

    // 部门成员列表
    public function members(int $org_id, int $department_id)
    {
        return DepartmentMember::query()->where('org_id', $org_id)->where('department_id', $department_id)->get(['department_id', 'account_id', 'org_id', 'is_leader']);
    }

    // 我所在的部门
    public function myDepartments()
    {
        $department_ids = array_map(function (DepartmentMember $member) {
            return $member->department_id;
        }, DepartmentMember::query()->where('account_id', Tools::Account()->id)->get(['department_id'])->all());
        return array_map(function (Department $department) {
            return [
                'id'      => $department->id,
                'pid'     => $department->pid,
                'org_id'  => $department->org_id,
                'name'    => $department->name,
                'code'    => $department->code,
                'extends' => $department->extends
            ];
        }, Department::query()->whereIn('id', $department_ids)->get()->all());
    }

    // 我所在的部门成员列表
    public function myDeptMembers()
    {
        $department_ids = array_column($this->myDepartments(), 'id');
        $members = DepartmentMember::query()->where('org_id', Tools::Org()->id)->get(['department_id', 'account_id']);
        $account_ids = [];
        foreach ($members as $member) {
            if (in_array($member->department_id, $department_ids)) {
                array_push($account_ids, $member->account_id);
            }
        }
        return array_map(function (Account $account) {
            return [
                'account_id' => $account->id,
                'realname'   => $account->user->realname,
                'extends'    => $account->extends
            ];
        }, Account::query()->with('user')->whereIn('id', $account_ids)->get()->all());
    }
}