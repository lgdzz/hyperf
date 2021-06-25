<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\Factory;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;
use lgdz\object\Body;
use lgdz\object\Query;

class UserService
{
    // 用户列表
    public function index(array $input)
    {
        $query = new Query($input);
        $paginate = User::query()->with('role')->when($query->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($query->username, function ($query, $value) {
            return $query->where('username', 'like', '%' . $value . '%');
        })->when($query->phone, function ($query, $value) {
            return $query->where('phone', 'like', '%' . $value . '%');
        })->when($query->role_id, function ($query, $value) {
            return $query->where('role_id', $value);
        })->where('id', '>', 1)->orderByDesc('id')->paginate($query->size);
        return Tools::P(
            $paginate,
            function (User $user) {
                $user->addHidden('password', 'salt');
                return $user;
            }
        );
    }

    // 用户创建
    public function create(array $input): User
    {
        $body = new Body($input);
        User::query()->where('username', $body->username)->first() && Tools::E("账号[{$body->username}]已注册");
        User::query()->where('phone', $body->phone)->first() && Tools::E("手机号[{$body->phone}]已注册");
        $user = new User;
        $user->phone = $body->phone;
        $user->username = $body->username;
        $user->password = $body->password ?: '123456';
        $user->status = $body->status ?: 1;
        $user->is_system = $body->is_system ?: 0;
        $user->remark = $body->remark ?: '';
        $user->role_id = $body->role_id;
        $user->save();
        return $user;
    }

    // 用户更新
    public function update(int $id, array $input): User
    {
        $body = new Body($input);
        $user = $this->user($this->findById($id));
        switch ($body->op) {
            // 重置密码
            case 'ResetPassword':
                $user->password = $body->password ?: '123456';
                $user->save();
                break;
            // 修改密码
            case 'ChangePassword':
                // 验证旧密码
                !Factory::container()->password->check($body->old_password, $user->salt, $user->password) && Tools::E('旧密码不正确');
                $user->password = $body->password;
                $user->save();
                break;
            // 常规编辑
            default:
                User::query()->where('username', $body->username)->where('id', '!=', $user->id)->first() && Tools::E("账号[{$body->username}]已注册");
                User::query()->where('phone', $body->phone)->where('id', '!=', $user->id)->first() && Tools::E("手机号[{$body->phone}]已注册");
                $user->phone = $body->phone;
                $user->username = $body->username;
                $user->password = $body->password ?: '123456';
                $user->status = $body->status ?: 1;
                $user->is_system = $body->is_system ?: 0;
                $user->remark = $body->remark ?: '';
                $user->role_id = $body->role_id;
                $user->save();
                break;
        }
        return $user;
    }

    // 用户删除
    public function delete(int $id)
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