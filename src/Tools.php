<?php

declare (strict_types=1);

namespace lgdz\hyperf;

use Closure;
use lgdz\hyperf\model\User;
use lgdz\object\Body;
use lgdz\object\Query;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Hyperf\Utils\Context;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Contract\PaginatorInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use lgdz\Factory;
use lgdz\lib\HttpResponse;
use lgdz\hyperf\exception\BusinessException;

/**
 * Class Tools 助手
 * @package lgdz\hyperf
 */
class Tools
{
    /**
     * @param User|null $user
     * @return User
     */
    public static function U(User $user = null): User
    {
        if (is_null($user)) {
            return Context::get('user');
        } else {
            Context::set('user', $user);
            return $user;
        }
    }

    /**
     * 数据库查询分页
     * @param PaginatorInterface $paginate
     * @param Closure|null $closure
     * @return array
     */
    public static function P(PaginatorInterface $paginate, Closure $closure = null): array
    {
        return [
            'total' => $paginate->total(),
            'pages' => $paginate->lastPage(),
            'items' => is_null($closure) ? $paginate->items() : array_map($closure, $paginate->items())
        ];
    }

    /**
     * 输入
     * @return RequestInterface|mixed
     */
    public static function I()
    {
        return ApplicationContext::getContainer()->get(RequestInterface::class);
    }

    /**
     * 输出
     * @return ResponseInterface|mixed
     */
    public static function O()
    {
        return ApplicationContext::getContainer()->get(ResponseInterface::class);
    }

    /**
     * 响应JSON
     * @return HttpResponse
     */
    public static function R(): HttpResponse
    {
        return static::F()->response;
    }

    /**
     * 缓存控制对象
     * @return mixed|\Psr\SimpleCache\CacheInterface
     */
    public static function C()
    {
        return ApplicationContext::getContainer()->get(\Psr\SimpleCache\CacheInterface::class);
    }

    /**
     * 缓存设置
     * @param string $key
     * @param $value
     * @param null $ttl
     * @return bool
     */
    public static function C_set(string $key, $value, $ttl = null): bool
    {
        try {
            return static::C()->set($key, $value, $ttl);
        } catch (InvalidArgumentException $e) {
            static::E($e->getMessage());
        }
    }

    /**
     * 缓存读取
     * @param $key
     * @return mixed
     */
    public static function C_get($key)
    {
        try {
            return static::C()->get($key);
        } catch (InvalidArgumentException $e) {
            static::E($e->getMessage());
        }
    }

    /**
     * 异常抛出
     * @param string $msg
     * @return mixed
     */
    public static function E(string $msg)
    {
        throw new BusinessException($msg);
    }

    /**
     * 接口正常返回
     * @param null $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function Ok($data = null)
    {
        return static::O()->json(
            static::R()->ok($data)
        );
    }

    /**
     * 接口异常返回
     * @param string $message
     * @param int $status
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function Bad(string $message = 'Error', int $status = 0)
    {
        return static::O()->json(
            static::R()->bad($message, $status)
        );
    }

    /**
     * 工具类
     * @return Factory
     */
    public static function F()
    {
        return Factory::container();
    }

    /**
     * @param string $name
     * @return LoggerInterface
     */
    public static function Log(string $name = 'log')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name);
    }

    public static function Query(array $append = [])
    {
        $query = new Query(Tools::I()->getQueryParams());
        if (!empty($append)) {
            foreach ($append as $key => $value) {
                $query->$key = $value;
            }
        }
        return $query;
    }

    public static function Body(array $append = [])
    {
        $body = new Body(Tools::I()->getParsedBody());
        if (!empty($append)) {
            foreach ($append as $key => $value) {
                $body->$key = $value;
            }
        }
        return $body;
    }

    public static function container()
    {
        return ApplicationContext::getContainer();
    }
}