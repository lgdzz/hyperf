<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\exception\JwtAuthException;
use lgdz\hyperf\service\AuthService;
use lgdz\hyperf\Tools;

class AuthUserPowerMiddleware implements MiddlewareInterface
{
    /**
     * @Value("lgdz.power")
     */
    private $config;

    /**
     * @Inject()
     * @var AuthService
     */
    protected $AuthService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $router = $request->getAttribute(Dispatched::class)->handler->route ?? null;
        is_null($router) && Tools::E('无效访问');
        if ($this->config['enable']) {
            // 权限是通过请求方式+路由进行匹配验证
            $power = sprintf('%s:%s', $request->getMethod(), $router);
            $powers = $this->AuthService->getPowers(Tools::Account()->id);
            if (empty($powers)) {
                throw new JwtAuthException('权限失效，请重新登录');
            } elseif (!in_array($power, $powers['powers'])) {
                Tools::E('无接口使用权限');
            }
        }
        return $handler->handle($request);
    }
}