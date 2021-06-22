<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\Factory;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;

class UserService
{
    public function index(array $input)
    {
        $size = $input['page_size'] ?? 10;
        $status = $input['status'] ?? false;
        $username = $input['username'] ?? false;
        $phone = $input['phone'] ?? false;
        $role_id = $input['role_id'] ?? false;

        $paginate = User::query()->with('role')->when($status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($username, function ($query, $value) {
            return $query->where('username', 'like', '%' . $value . '%');
        })->when($phone, function ($query, $value) {
            return $query->where('phone', 'like', '%' . $value . '%');
        })->when($role_id, function ($query, $value) {
            return $query->where('role_id', $value);
        })->where('id', '>', 1)->orderByDesc('id')->paginate((int)$size);

        return Tools::P(
            $paginate,
            function (User $user) {
                $user->addHidden('password', 'salt');
                return $user;
            }
        );
    }

    public function create(array $input): User
    {
        $username = $input['username'];
        $phone = $input['phone'];
        User::query()->where('username', $username)->first() && Tools::E("账号[{$username}]已注册");
        User::query()->where('phone', $phone)->first() && Tools::E("手机号[{$phone}]已注册");
        $user = new User;
        $user->phone = $phone;
        $user->username = $username;
        $user->password = $input['password'] ?? '123456';
        $user->status = $input['status'] ?? 1;
        $user->is_system = $input['is_system'] ?? 0;
        $user->remark = $input['remark'] ?? '';
        $user->role_id = $input['role_id'];
        $user->save();
        return $user;
    }

    public function update(int $id, array $input): User
    {
        $user = $this->user($this->findById($id));
        switch ($input['op']) {
            // 重置密码
            case 'ResetPassword':
                $user->password = $input['password'] ?? '123456';
                $user->save();
                break;
            // 修改密码
            case 'ChangePassword':
                // 验证旧密码
                !Factory::container()->password->check($input['old_password'], $user->salt, $user->password) && Tools::E('旧密码不正确');
                $user->password = $input['password'];
                $user->save();
                break;
            // 常规编辑
            default:
                $username = $input['username'];
                $phone = $input['phone'];
                User::query()->where('username', $username)->where('id', '!=', $user->id)->first() && Tools::E("账号[{$username}]已注册");
                User::query()->where('phone', $phone)->where('id', '!=', $user->id)->first() && Tools::E("手机号[{$phone}]已注册");
                $user->phone = $phone;
                $user->username = $username;
                $user->password = $input['password'] ?? '123456';
                $user->status = $input['status'] ?? 1;
                $user->is_system = $input['is_system'] ?? 0;
                $user->remark = $input['remark'] ?? '';
                $user->role_id = $input['role_id'];
                $user->save();
                break;
        }
        return $user;
    }

    public function delete(int $id, ...$args)
    {
        try {
            $this->findById($id)->delete();
        } catch (\Exception $e) {
            Tools::E('删除失败');
        }
    }

    public function findById(int $id)
    {
        return User::query()->where('id', $id)->first();
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
}