<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Closure;
use Hyperf\Utils\ApplicationContext;
use lgdz\exception\BusinessException;
use lgdz\Factory;
use lgdz\hyperf\model\{Account, Role, Rule, User};
use lgdz\hyperf\Tools;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

abstract class AbstractAuthService
{
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
        $this->secret = config('lgdz.auth.secret', '1234567890');
        $this->ticket_key = config('lgdz.auth.ticket_key', 'user_ticket');
        $this->sso = config('lgdz.auth.sso', false);
        $this->redis = ApplicationContext::getContainer()->get(Redis::class);

        $this->jwt = Factory::container()->jwt;
        $this->jwt->setSecret($this->secret);
        $this->jwt->setTicketKey($this->ticket_key);
        $this->jwt->setRedis($this->redis);
    }

    /**
     * @param \lgdz\lib\JwtAuth $jwt
     */
    public function setJwt(\lgdz\lib\JwtAuth $jwt): void
    {
        $this->jwt = $jwt;
    }

    /**
     * @param mixed $secret
     */
    public function setSecret($secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @param mixed $ticket_key
     */
    public function setTicketKey($ticket_key): void
    {
        $this->ticket_key = $ticket_key;
    }

    /**
     * @param Redis|mixed $redis
     */
    public function setRedis($redis): void
    {
        $this->redis = $redis;
    }

    /**
     * 登录生成凭证
     * @param int $user_id
     * @param array $body
     * @return array
     */
    public function issueAuthorization(int $user_id, array $body = []): array
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
    public function loginByUsername(string $username, string $password, string $res = 'loginResult'): array
    {
        $user = User::query()->where('username', $username)->orWhere('phone', $username)->first();
        if (!($user instanceof User)) {
            Tools::E('账号或密码不正确');
        } elseif (!$user->checkPassword($password)) {
            Tools::E('账号或密码不正确');
        } elseif ($user->is_disable) {
            Tools::E('账号已停用');
        } else {
            return $this->$res($user);
        }
    }

    // 登录成功返回
    protected function loginResult(User $user): array
    {
        // 生成token
        [$token, $expire_at] = $this->issueAuthorization($user->id);
        // 生成路由菜单
        $router_config = $this->getRouterConfig($user);
        $result = [];
        $result['user'] = [
            'user_id'  => $user->id,
            'username' => $user->username,
            'phone'    => $user->phone,
            'type'     => $user->type
        ];
        $result['token'] = $token;
        $result['expire_at'] = $expire_at;
        return $result;
    }

    // 退出登录
    public function logout(int $uid): void
    {
        $this->redis->hDel($this->ticket_key, (string)$uid);
    }

    protected function powerHashKey(...$args)
    {
        [$user_id] = $args;
        return (string)$user_id;
    }

    // 获取客户端路由配置
    abstract public function getRouterConfig(int $account_id): array;

    // 用户账户权限集KEY
    abstract public function powerKey(): string;


    /**
     * 获取角色完整权限规则列表
     * @param User $user
     * @return array
     */
    public function getRoleRules(Role $role)
    {
        // 系统超级账号
        if ($role->master) {
            return Rule::query()->orderByRaw('sort asc,id asc')->get()->toArray();
        } else {
            $rule_ids = Rule::fullRulesIds($rule_ids);
            return Rule::query()->orderByRaw('sort asc,id asc')->find($rule_ids)->toArray();
        }
    }

    public function getPowers(int $user_id)
    {
        $values = $this->redis->hGet($this->powerKey(), $this->powerHashKey($user_id));
        return $values === false ? [] : unserialize($values);
    }

    // 写入powers到redis
    public function setPowers(array $api_list, int $user_id)
    {
        $powers = array_map(function ($item) {
            return $item['method'] . ':' . $item['service_router'];
        }, $api_list);

        $this->redis->hSet($this->powerKey(), $this->powerHashKey($user_id), serialize([
            'user_id' => $user_id,
            'powers'  => $powers
        ]));
    }
}