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
        if ($input->username) {
            $input->user_id = array_column(User::query()->where('username', 'like', '%' . $input->username . '%')->get(['id'])->toArray(), 'id') ?: [-1];
        }
        if (!empty($with)) {
            $model = Account::query()->with(...$with);
        } else {
            $model = Account::query();
        }
        // 非root账号登录时隐藏root在组织用户中的记录
        if (Tools::U()->id !== 1) {
            $model = $model->where('user_id', '!=', 1);
        }
        $paginate = $model->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->org_id, function ($query, $value) {
            if (!Tools::IsTargetParentOrg((int)$value)) {
                $value = 0;
            }
            return $query->where('org_id', $value);
        })->when($input->user_id, function ($query, $value) {
            return $query->whereIn('user_id', is_array($value) ? $value : [$value]);
        })->when($input->role_id, function ($query, $value) {
            return $query->where('role_id', $value);
        })->orderByRaw('id desc')->paginate($input->size);
        return Tools::P($paginate, $callback);
    }

    // 绑定用户
    private function bindUser(Body $input, User $user)
    {
        // 检查是否开启多账户绑定
        !config('lgdz.account.many') && Account::query()->where('user_id', $user->id)->exists() && Tools::E('账号已存在，系统未开启多账户绑定');
        // 组织+用户+角色必须唯一
        if ($input->role_id > 0) {
            $account = Account::query()->where('org_id', $input->org_id)->where('user_id', $user->id)->where('role_id', $input->role_id)->first();
        } else {
            $account = Account::query()->where('org_id', $input->org_id)->where('user_id', $user->id)->first();
        }
        if ($account instanceof Account) {
            return $account;
        }
        $account = new Account();
        $account->org_id = $input->org_id;
        $account->user_id = $user->id;
        $account->role_id = $input->role_id;
        $account->status = $input->status ?? 1;
        $account->extends = $input->extends;
        $account->save();
        return $account;
    }

    public function create(Body $input): Account
    {
        if (!$input->org_id) {
            $input->org_id = Tools::Org()->id;
        }
        // 验证角色所属组织
        $input->role_id && $this->validationOrgIdAndRoleId($input->org_id, $input->role_id);
        $user_service = Tools::container()->get(UserService::class);
        $user = $user_service->findByUsername($input->username);

        // 手动控制创建方式
        if ($input->type) {
            return $this->createManualControl($input, $user);
        }

        // 自动判断创建方式
        if ($user instanceof User) {
            return $this->bindUser($input, $user);
        } else {
            // 无账号，创建
            if (!$input->from_id) {
                $input->from_id = $input->org_id;
            }
            $user_service->create(new Body(['username' => $input->username, 'password' => $input->password, 'phone' => $input->phone, 'realname' => $input->realname, 'from_channel' => User::FROM_CHANNEL_ORG, 'from_id' => $input->from_id, 'extends' => $input->extends]));
            return $this->create($input);
        }
    }

    // 手动控制是注册还是关联
    public function createManualControl(Body $input, $user): Account
    {
        switch ($input->type) {
            case 'reg':
                // 验证密码强度
                Tools::F()->password->checkStrength($input->password, $input->username);
                // 创建账号
                $user = Tools::container()->get(UserService::class)->create(new Body(['username' => $input->username, 'password' => $input->password, 'phone' => $input->phone, 'realname' => $input->realname, 'from_channel' => User::FROM_CHANNEL_ORG, 'from_id' => $input->org_id, 'extends' => $input->extends]));
                return $this->bindUser($input, $user);
            case 'bind':
                if ($user instanceof User) {
                    return $this->bindUser($input, $user);
                }
                Tools::E('账号不存在，关联失败');
            default:
                Tools::E('不安全的创建方式');
        }
    }

    public function update(int $id, Body $input)
    {
        $account = $this->account($this->findById($id));
        $this->validationOrgIdAndRoleId($account->org_id, $input->role_id);
        $account->role_id = $input->role_id;
        $account->status = $input->status;
        $account->extends = $input->extends;
        $account->save();
        // 未开启多账户时，同时编辑账号信息
        if (!config('lgdz.account.many')) {
            Tools::container()->get(UserService::class)->update($account->user_id, $input);
        }
    }

    public function delete(int $id)
    {
        $account = $this->account($this->findById($id));
        Tools::IsTargetParentOrg($account->org_id) && $account->delete();
        // 未开启多账户时，同时删除账号信息
        if (!config('lgdz.account.many')) {
            Tools::container()->get(UserService::class)->delete($account->user_id);
        }
    }

    // 验证org_id和role_id的有效性
    public function validationOrgIdAndRoleId(int $org_id, int $role_id): void
    {
        !Tools::IsTargetParentOrg($org_id) && Tools::E('您没有对该组织操作权限');
        $role_service = Tools::container()->get(RoleService::class);
        $role = $role_service->role($role_service->findById($role_id));
        if ($role->org_id !== $org_id && Tools::DefaultOrgAdminRoleId($org_id) !== $role->id) {
            Tools::E('该角色不属于该组织');
        }
    }

    public function findById(int $id, array $with = [])
    {
        if (!empty($with)) {
            return Account::query()->with(...$with)->where('id', $id)->first();
        } else {
            return Account::findFromCache($id);
        }
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

    public function select(int $org_id)
    {
        $model = Account::query()->with(['user', 'role']);
        // 非root账号登录时隐藏root在组织用户中的记录
        if (Tools::U()->id !== 1) {
            $model = $model->where('user_id', '!=', 1);
        }
        $list = $model->where('status', 1)->where('org_id', $org_id)->get();
        return array_map(function (Account $row) {
            return [
                'account_id' => $row->id,
                'org_id' => $row->org_id,
                'user_id' => $row->user->id,
                'realname' => $row->user->realname,
                'username' => $row->user->username,
                'phone' => $row->user->phone,
                'role_name' => $row->role->name ?? ''
            ];
        }, $list->all());
    }
}