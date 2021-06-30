<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use lgdz\exception\JwtAuthException;
use lgdz\hyperf\service\AuthService;

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
        // 用户信息保存到上下文
        $this->user($body->uid);
        return $handler->handle($request);
    }

    public function user(int $user_id)
    {
        Tools::U(User::query()->where('id', $user_id)->first());
    }
}