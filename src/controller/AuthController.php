<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

//use App\Utils\Tools;
//use App\Request\LoginRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

//use App\Service\{AuthService, LoginLogService, UserService};
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use lgdz\hyperf\service\AuthService;

class AuthController
{
    /**
     * @Inject
     * @var AuthService
     */
    protected $AuthService;

    /**
     * @Inject
     * @var UserService
     */
    protected $UserService;

    // 账号密码登录
    public function login(LoginRequest $request, ResponseInterface $response)
    {
        $input = $request->getParsedBody();
        // 检查验证码
//        Tools::$factory->Captcha()->check($input['code'] ?? []);
        $result = $this->AuthService->loginByUsername($input['username'], $input['password']);
        // 记录登录日志
        LoginLogService::create($result['user']['user_id'], 'backstage');
        return $response->json(Tools::R()->success('登录成功', $result));
    }

    // 修改登录密码
    public function changePwd(RequestInterface $request, ResponseInterface $response)
    {
        $this->UserService->update(
            Tools::U()->user_id,
            array_merge(
                $request->getParsedBody(),
                [
                    'op' => 'ChangePassword'
                ]
            )
        );
        return $response->json(Tools::R('修改登录密码')->ok());
    }

    // 退出登录
    public function logout(RequestInterface $request, ResponseInterface $response)
    {
        $this->AuthService->logout();
        return $response->json(Tools::R()->ok());
    }

    // 获取路由
    public function router(ResponseInterface $request, ResponseInterface $response)
    {
        return $response->json(Tools::R()->ok([]));
    }

    public function test()
    {
        var_dump(config('lgdz.auth.secret'));
        return 'success';
    }
}
