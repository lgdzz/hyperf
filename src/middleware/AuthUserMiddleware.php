<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\exception\JwtAuthException;
use lgdz\hyperf\service\AuthService;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;

class AuthUserMiddleware implements MiddlewareInterface
{
    /**
     * @Inject()
     * @var AuthService
     */
    protected $AuthService;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('authorization');
        if (empty($token)) {
            throw new JwtAuthException('未登录');
        }
        // 验证登录信息
        $body = $this->AuthService->checkAuthorization((string)$token);
        Tools::Context('auth_user_body', $body);

        // 记录请求日志
        go(function () use ($request) {
            $router = $request->getAttribute(Dispatched::class)->handler->route ?? null;
            $path = $request->getUri()->getPath();
            $method = $request->getMethod();
            $body = $request->getMethod() === 'GET' ? $request->getQueryParams() : $request->getParsedBody();
            Tools::Log('request')->info(sprintf('[%s:%s][PATH:%s]', $method, $router, $path), $body);
        });

        // 用户信息保存到上下文
        $this->setUser($body->uid);
        return $handler->handle($request);
    }

    public function setUser(int $user_id)
    {
        Tools::U(User::findFromCache($user_id));
    }
}