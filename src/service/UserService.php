<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\DbConnection\Db;
use lgdz\Factory;
use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\User;
use lgdz\hyperf\model\UserRole;
use lgdz\hyperf\Tools;
use lgdz\object\Body;
use lgdz\object\Query;

class UserService
{
    // 用户列表
    public function index(Query $input)
    {
        $paginate = User::query()->with('role')->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->site_id, function ($query, $value) {
            return $query->where('site_id', $value);
        })->when($input->username, function ($query, $value) {
            return $query->where('username', 'like', '%' . $value . '%');
        })->when($input->phone, function ($query, $value) {
            return $query->where('phone', 'like', '%' . $value . '%');
        })->when($input->role_id, function ($query, $value) {
            return $query->where('role_id', $value);
        })->where('id', '>', 1)->orderByDesc('id')->paginate($input->size);
        return Tools::P(
            $paginate,
            function (User $user) {
                $user->hiddenPassword();
                return $user;
            }
        );
    }

    // 用户创建
    public function create(Body $input)
    {
        $site_id = $input->site_id ?? 0;
        User::query()->where('username', $input->username)->first() && Tools::E("账号[{$input->username}]已注册");
        User::query()->where('phone', $input->phone)->first() && Tools::E("手机号[{$input->phone}]已注册");
        $this->checkRoleSite($site_id, $input->role_ids);
        Db::transaction(function () use ($input, $site_id) {
            $user = new User;
            $user->site_id = $site_id;
            $user->phone = $input->phone ?: '';
            $user->username = $input->username;
            $user->password = $input->password ?: '123456';
            $user->status = $input->status ?: 1;
            $user->is_system = $input->is_system ?: 0;
            $user->remark = $input->remark ?: '';
            $user->save();
            foreach ($input->role_ids as $role_id) {
                $user_role = new UserRole();
                $user_role->user_id = $user->id;
                $user_role->role_id = $role_id;
                $user_role->save();
            }
        });
    }

    // 用户更新
    public function update(int $id, Body $input)
    {
        $user = $this->user($this->findById($id));
        switch ($input->op) {
            // 重置密码
            case 'ResetPassword':
                $user->password = $input->password ?: '123456';
                $user->save();
                break;
            // 修改密码
            case 'ChangePassword':
                // 验证旧密码
                !$user->checkPassword($input->old_password ?: '') && Tools::E('旧密码不正确');
                $user->password = $input->password;
                $user->save();
                break;
            // 常规编辑
            default:
                User::query()->where('username', $input->username)->where('id', '!=', $user->id)->first() && Tools::E("账号[{$input->username}]已注册");
                User::query()->where('phone', $input->phone)->where('id', '!=', $user->id)->first() && Tools::E("手机号[{$input->phone}]已注册");
                $this->checkRoleSite($user->site_id, $input->role_ids);
                Db::transaction(function () use ($user) {
                    $user->phone = $input->phone;
                    $user->username = $input->username;
                    $user->password = $input->password ?: '123456';
                    $user->status = $input->status ?: 1;
                    $user->is_system = $input->is_system ?: 0;
                    $user->remark = $input->remark ?: '';
                    $user->save();
                    $user->roles()->delete();
                    foreach ($input->role_ids as $role_id) {
                        $user_role = new UserRole();
                        $user_role->user_id = $user->id;
                        $user_role->role_id = $role_id;
                        $user_role->save();
                    }
                });
                break;
        }
    }

    // 用户删除
    public function delete(int $id)
    {
        $this->user($this->findById($id))->delete();
    }

    public function findById(int $id)
    {
        return User::query()->where('id', $id)->where('site_id', Tools::SiteId())->first();
    }

    public function findByUsername(string $username)
    {
        return User::query()->where('username', $username)->first();
    }

    public function findByPhone(string $phone)
    {
        return User::query()->where('phone', $phone)->first();
    }

    /**
     * 验证参数是否是User对象，如不是抛出异常
     * @param $user
     * @return User
     */
    public function user($user): User
    {
        return ($user instanceof User) ? $user : Tools::E('用户不存在');
    }

    public function checkRoleSite(int $site_id, array $role_ids)
    {
        foreach ($role_ids as $role_id) {
            $role_site_id = Role::query()->where('id', $role_id)->value('site_id');
            if ($role_site_id !== $site_id) {
                Tools::E('角色不存在');
            }
        }
    }
}