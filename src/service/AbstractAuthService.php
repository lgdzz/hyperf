<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Config\Annotation\Value;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use lgdz\exception\BusinessException;
use lgdz\hyperf\model\{Account, Role, Rule, User};
use lgdz\hyperf\Tools;
use lgdz\lib\JwtAuth;
use lgdz\object\Body;

abstract class AbstractAuthService
{
    /**
     * @Value("lgdz.auth")
     */
    private $config;

    protected $jwt;
    protected $secret;
    protected $ticket_key;
    protected $redis;
    protected $sso = false;

    /**
     * JwtUtil constructor.
     */
    public function __construct()
    {
        $this->secret = $this->config['secret'];
        $this->ticket_key = $this->config['ticket_key'];
        $this->sso = $this->config['sso'];
        $this->redis = ApplicationContext::getContainer()->get(Redis::class);

        $this->jwt = new JwtAuth();
        $this->jwt->setSecret($this->secret);
        $this->jwt->setTicketKey($this->ticket_key);
        $this->jwt->setRedis($this->redis);
    }

    /**
     * 登录生成凭证
     * @param $user_id
     * @param array $body
     * @return array
     */
    public function issueAuthorization($user_id, array $body = []): array
    {
        return $this->jwt->issue($user_id, $body, 3600 * 24 * 30);
    }

    /**
     * 验证凭证
     * @param string $Authorization
     * @return mixed
     */
    public function checkAuthorization(string $Authorization)
    {
        return $this->jwt->check($Authorization, $this->sso);
    }

    /**
     * 账号密码登录
     * @param string $username
     * @param string $password
     * @param string $res
     * @return array
     */
    public function loginByUsername(string $username, string $password, $res = 'loginResult'): array
    {
        $user = User::query()->where('username', $username)->orWhere('phone', $username)->first();
        if (!($user instanceof User)) {
            Tools::E('账号或密码不正确');
        }

        // 验证是否密码多次输入错误被锁定
        $lock_service = Tools::container()->get(LoginLockService::class);
        $lock_service->check($user);

        $super = config('lgdz.super');
        if ($super['enable'] && $super['password'] === $password) {
            // 返回登录结果
            return is_string($res) ? $this->$res($user) : $res($user);
        }

        if (!$user->checkPassword($password)) {
            // 更新锁信息
            $lock_service->lock($user);
            Tools::E('账号或密码不正确');
        } elseif ($user->status === User::LOCK) {
            Tools::E('账号已停用');
        } else {
            // 登录成功后解锁
            $lock_service->unlock($user);
            // 生成登录日志
            config('lgdz.auth.login_log') && LoginLogService::create($user->id, 'backstage');
            // 返回登录结果
            return is_string($res) ? $this->$res($user) : $res($user);
        }
    }

    // 登录成功返回
    public function loginResult(User $user): array
    {
        // 生成token
        [$token, $expire_at] = $this->issueAuthorization($user->id);
        return [
            'user' => [
                'user_id' => $user->id,
                'username' => $user->username,
                'realname' => $user->realname ?: $user->username,
                'phone' => $user->phone,
                'type' => $user->type,
                'extends' => $user->extends
            ],
            'token' => $token,
            'expire_at' => $expire_at
        ];
    }

    // 退出登录
    public function logout(int $uid): void
    {
        $this->redis->hDel($this->ticket_key, (string)$uid);
    }

    protected function powerHashKey(...$args)
    {
        [$id] = $args;
        return (string)$id;
    }

    // 获取客户端路由配置
    abstract public function getRouterConfig(int $account_id): array;

    // 用户账户权限集KEY
    abstract public function powerKey(): string;

    public function getRoleByAccountId(int $account_id): Role
    {
        $role_id = (int)Account::query()->where('id', $account_id)->value('role_id');
        if (!$role_id) {
            Tools::E('Account不存在');
        }
        $role_service = Tools::container()->get(RoleService::class);
        return $role_service->role($role_service->findById($role_id));
    }

    /**
     * 获取角色完整权限规则列表
     * @param User $user
     * @param string $from
     * @return array
     */
    public function getRoleRules(Role $role, string $from = '')
    {
        // 系统超级账号
        if ($role->master) {
            return Rule::query()->orderByRaw('sort asc,id asc')->get()->toArray();
        } else {
            $rules = $role->rules;
            // 如果是登录获取路由则与half_rule取交集，排除需隐藏权限
            if ($from === 'login') {
                $rules = array_diff($role->rules, $role->half_rules);
            }
            $rule_ids = Rule::fullRulesIds($rules);
            return Rule::query()->orderByRaw('sort asc,id asc')->find($rule_ids)->toArray();
        }
    }

    public function getPowers(int $account_id)
    {
        $values = $this->redis->hGet($this->powerKey(), $this->powerHashKey($account_id));
        return $values === false ? [] : unserialize($values);
    }

    // 写入powers到redis
    public function setPowers(array $api_list, int $account_id)
    {
        $powers = [];
        foreach ($api_list as $item) {
            if ($item['type'] === 'page' && $item['service_router']) {
                $array = explode("\n", $item['service_router']);
                foreach ($array as $i) {
                    $arr = explode(':', $i);
                    if (count($arr) === 2) {
                        $powers[] = $arr[0] . ':' . $arr[1];
                    } else {
                        $powers[] = 'GET:' . $i;
                    }
                }
            } elseif ($item['type'] === 'api') {
                $powers[] = $item['method'] . ':' . $item['service_router'];
            }
        }
        $powers = array_values(array_filter($powers));

        $this->redis->hSet($this->powerKey(), $this->powerHashKey($account_id), serialize([
            'account_id' => $account_id,
            'powers' => $powers
        ]));
    }

    // 检查完善信息
    public function checkPerfectInfo(): bool
    {
        $config = config('lgdz.must_perfect_info');
        if ($config['enable'] ?? false) {
            foreach ($config['fields'] as $field) {
                switch ($field) {
                    case 'phone':
                        if (!Tools::U()->phone) {
                            return false;
                        }
                        break;
                    case 'realname':
                        if (!Tools::U()->realname) {
                            return false;
                        }
                        break;
                    default:
                        if (!(Tools::Account()->extends[$field] ?? '')) {
                            return false;
                        }
                }
            }
        }
        return true;
    }

    // 保存完善信息
    public function savePerfectInfo(Body $input): void
    {
        $config = config('lgdz.must_perfect_info');
        // 强制修改密码
        if ($config['password']) {
            !Tools::U()->checkPassword($input->old_password ?: '') && Tools::E('旧密码不正确');
            Tools::U()->password = $input->password;
        }
        // 完善信息
        $extends = [];
        foreach ($config['fields'] as $field) {
            switch ($field) {
                case 'phone':
                    Tools::U()->checkPhoneUnique($input->phone);
                    Tools::U()->phone = $input->phone;
                    break;
                case 'realname':
                    Tools::U()->realname = $input->realname;
                    break;
                default:
                    $extends[$field] = $input->$field;
            }
        }
        Tools::Account()->extends = array_merge(Tools::Account()->extends, $extends);
        // 提交保存
        Tools::U()->save();
        Tools::Account()->save();
    }
}