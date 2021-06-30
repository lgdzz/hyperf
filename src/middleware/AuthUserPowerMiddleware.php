<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use lgdz\exception\JwtAuthException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\hyperf\service\AuthService;

class AuthUserPowerMiddleware implements MiddlewareInterface
{
    /**
     * @Inject()
     * @var AuthService
     */
    protected $AuthService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $router = $request->getAttribute(Dispatched::class)->handler->route ?? null;
        if (is_null($router)) {
            throw new BusinessException('无效访问');
        }
        $power = $request->getMethod() . ':' . $router;
        $powers = $this->AuthService->getPowers(Tools::U()->user_id);
        if (empty($powers)) {
//            throw new JwtAuthException('权限失效，请重新登录');
        } elseif (!in_array($power, $powers['powers'])) {
//            throw new BusinessException('无接口使用权限');
        }
        return $handler->handle($request);
    }
}