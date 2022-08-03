<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;

class LoginLockService
{
    private $enable = false; // 是否开启密码错误锁定
    private $bad = 3; // 密码错误次数
    private $time = 5; // 锁定时长（分钟）
    private $key = 'user_lock';

    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    public function __construct()
    {
        $config = config('lgdz.auth.lock');
        $this->enable = $config['enable'] ?? false;
        $this->bad = $config['bad'] ?? 3;
        $this->time = $config['time'] ?? 5;
        $this->key = $config['key'] ?? 'user_lock';
    }

    private function data(string $username): array
    {
        $data = $this->redis->hGet($this->key, $username);
        if ($data) {
            $data = unserialize($data);
            // 当前时间>=解锁时间时直接解锁
            if (time() >= $data['unlock_time']) {
                $data['status'] = 'unlock';
            }
        } else {
            $data = [
                'status' => 'unlock', // lock
                'bad' => 0,
                'unlock_time' => 0
            ];
        }
        return $data;
    }

    public function isLock(string $username): bool
    {
        if ($this->enable) {
            $data = $this->data($username);
            if ($data['status'] === 'lock') {
                return true;
            }
        }

        return false;
    }

    public function check(User $user): void
    {
        if ($this->enable) {
            $data = $this->data($user->username);
            if ($data['status'] === 'lock') {
                Tools::E(sprintf('您的账号由于多次密码输入错误，现锁定%s分钟，预计%s后自动解锁或联系管理员', $this->time, date('H:i:s', $data['unlock_time'])));
            }
        }
    }

    public function lock(User $user): void
    {
        if ($this->enable) {
            $data = $this->data($user->username);
            $data['bad']++;
            if ($data['bad'] >= $this->bad) {
                $data['status'] = 'lock';
                $data['unlock_time'] = strtotime('+' . $this->time . ' minute');
            }
            $this->redis->hSet($this->key, $user->username, serialize($data));
        }
    }

    public function unlock(User $user): void
    {
        $this->redis->hDel($this->key, $user->username);
    }
}