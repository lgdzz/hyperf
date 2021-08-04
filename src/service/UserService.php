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
        $paginate = User::query()->when($input->status, function ($query, $value) {
            return $query->where('status', $value);
        })->when($input->username, function ($query, $value) {
            return $query->where('username', 'like', '%' . $value . '%');
        })->when($input->phone, function ($query, $value) {
            return $query->where('phone', 'like', '%' . $value . '%');
        })->where('type', 'branch')->orderByDesc('id')->paginate($input->size);
        return Tools::P(
            $paginate,
            function (User $user) {
                $user->append('account_count');
                $user->hiddenPassword();
                return $user;
            }
        );
    }

    // 用户创建
    public function create(Body $input, string $type = 'branch')
    {
        $site_id = $input->site_id ?? 0;
        User::query()->where('username', $input->username)->first() && Tools::E("账号[{$input->username}]已注册");
        User::query()->where('phone', $input->phone)->first() && Tools::E("手机号[{$input->phone}]已注册");
        $user = new User;
        $user->type = $type;
        $user->phone = $input->phone ?: '';
        $user->username = $input->username;
        $user->password = $input->password ?: '123456';
        $user->status = $input->status ?: 1;
        $user->is_system = $input->is_system ?: 0;
        $user->remark = $input->remark ?: '';
        $user->save();
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
                $user->status = $input->status ?: 1;
                $user->remark = $input->remark ?: '';
                $user->save();
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