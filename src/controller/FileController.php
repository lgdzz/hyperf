<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Utils\ApplicationContext;
use lgdz\Factory;
use lgdz\hyperf\Tools;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\service\UserService;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\validator\UserValidator;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class FileController
{

}
