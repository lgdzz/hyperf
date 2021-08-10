<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Account;
use lgdz\hyperf\model\Organization;
use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\User;
use lgdz\object\Body;
use lgdz\object\Query;
use lgdz\hyperf\Tools;

class AccountService
{
    public function index(Query $input, array $with = [], \Closure $callback = null)
    {
        if (!empty($with)) {
            $model = Account::query()->with(...$with);
        } else {
            $model = Account::query();
        }
        $paginate = $model->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->org_id, function ($query, $value) {
            if (!Tools::IsTargetParentOrg((int)$value)) {
                $value = 0;
            }
            return $query->where('org_id', $value);
        })->when($input->user_id, function ($query, $value) {
            return $query->where('user_id', $value);
        })->when($input->role_id, function ($query, $value) {
            return $query->where('role_id', $value);
        })->orderByRaw('id desc')->paginate($input->size);
        return Tools::P($paginate, $callback);
    }

    public function create(Body $input)
    {
        if (!$input->org_id) {
            $input->org_id = Tools::Org()->id;
        }
        $this->validationOrgIdAndRoleId($input->org_id, $input->role_id);
        $user_service = Tools::container()->get(UserService::class);
        $user = $user_service->findByUsername($input->username);
        if ($user instanceof User) {
            $account = new Account();
            $account->org_id = $input->org_id;
            $account->user_id = $user->id;
            $account->role_id = $input->role_id;
            $account->status = $input->status;
            $account->save();
        } else {
            // 无账号，创建
            $user_service->create(new Body(['username' => $input->username]));
            $this->create($input);
        }
    }

    public function update(int $id, Body $input)
    {
        $account = $this->account($this->findById($id));
        $this->validationOrgIdAndRoleId($account->org_id, $input->role_id);
        $account->role_id = $input->role_id;
        $account->status = $input->status;
        $account->save();
    }

    public function delete(int $id)
    {
        $account = $this->account($this->findById($id));
        Tools::IsTargetParentOrg($account->org_id) && $account->delete();
    }

    // 验证org_id和role_id的有效性
    public function validationOrgIdAndRoleId(int $org_id, int $role_id): void
    {
        !Tools::IsTargetParentOrg($org_id) && Tools::E('您没有对该组织操作权限');
        $role_service = Tools::container()->get(RoleService::class);
        $role = $role_service->role($role_service->findById($role_id));
        if ($role->org_id !== $org_id) {
            Tools::E('该角色不属于该组织');
        }
    }

    public function findById(int $id)
    {
        return Account::query(true)->where('id', $id)->first();
    }

    /**
     * 验证参数是否是Account对象，如不是抛出异常
     * @param $account
     * @return Account
     */
    public function account($account): Account
    {
        return ($account instanceof Account) ? $account : Tools::E('账户不存在');
    }
}