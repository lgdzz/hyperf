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
                $user->append('account_count', 'last_time_format');
                $user->hiddenPassword();
                return $user;
            }
        );
    }

    // 用户创建
    public function create(Body $input, string $type = 'branch'): User
    {
        User::query()->where('username', $input->username)->first() && Tools::E("账号[{$input->username}]已注册");
        $input->phone && User::query()->where('phone', $input->phone)->first() && Tools::E("手机号[{$input->phone}]已注册");
        $user = new User;
        $user->type = $type;
        $user->phone = $input->phone ?: '';
        $user->realname = $input->realname ?: '';
        $user->username = $input->username;
        $user->password = $input->password ?: '112233';
        // 验证密码强度
        $input->password && $user->checkPasswordStrength($input->password);
        $user->status = $input->status ?: 1;
        $user->is_system = $input->is_system ?: 0;
        $user->remark = $input->remark ?: '';
        $user->from_channel = $input->from_channel ?? 0;
        $user->from_id = $input->from_id ?? 0;
        $user->extends = $input->extends;
        $user->save();
        return $user;
    }

    // 用户更新
    public function update(int $id, Body $input, bool $checkRight = true)
    {
        $user = $this->user($this->findById($id));

        if ($checkRight && $user->from_channel === '组织' && $user->from_id !== Tools::Org()->id) {
            Tools::E('只有账号注册单位才能管理此账号');
        }

        switch ($input->op) {
            // 重置密码
            case 'ResetPassword':
                $user->password = $input->password ?: '112233';
                $user->save();
                break;
            // 修改密码
            case 'ChangePassword':
                // 验证旧密码
                !$user->checkPassword($input->old_password ?: '') && Tools::E('旧密码不正确');
                // 验证新密码强度
                $user->checkPasswordStrength($input->password);
                $user->password = $input->password;
                $user->save();
                break;
            // 常规编辑
            default:
                if ($input->username) {
                    User::query()->where('username', $input->username)->where('id', '!=', $user->id)->first() && Tools::E("账号[{$input->username}]已注册");
                    $user->username = $input->username;
                }
                if ($input->phone) {
                    $user->checkPhoneUnique($input->phone);
                    $user->phone = $input->phone;
                }
                if ($input->password) {
                    // 验证密码强度
                    $input->password && $user->checkPasswordStrength($input->password);
                    $user->password = $input->password;
                }
                $user->realname = $input->realname ?: '';
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