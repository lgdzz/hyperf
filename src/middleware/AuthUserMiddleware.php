<?php

declare(strict_types=1);

namespace lgdz\hyperf\middleware;

use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;
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
        // 记录用户信息
//        $user_info = new UserInfo();
//        $user_info->user_id = $body->uid;
//        $user_info->username = $body->username;
//        $user_info->role_id = $body->role_id;
//        $user_info->parent_role_id = $body->parent_role_id;
//        $user_info->user_type = $body->type;
//        $user_info->org_id = $body->org_id;
//        $user_info->agency_id = $body->agency_id;
//        $user_info->org_pid = $body->org_pid;
//        $user_info->grid_level_id = $body->grid_level_id;
//        $user_info->is_team = $body->is_team;
//        Context::set('user_info', $user_info);
        // 生成操作日志
//        Context::set('oplog', [
//            'user_id'    => $user_info->user_id,
//            'browser'    => $request->getHeaderLine('user-agent'),
//            'start_time' => microtime(true)
//        ]);
        return $handler->handle($request);
    }
}