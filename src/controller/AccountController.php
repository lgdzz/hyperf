<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\OrgMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\service\AccountService;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\validator\AccountValidator;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class),
 *     @Middleware(OrgMiddleware::class)
 * })
 */
class AccountController
{
    /**
     * @Inject
     * @var AccountService
     */
    protected $AccountService;

    /**
     * @RequestMapping(path="/l/account", methods="get")
     */
    public function index()
    {
        $result = $this->AccountService->index(Tools::Query(), ['role', 'org', 'user']);
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/account/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->AccountService->account($this->AccountService->findById($id));
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/account", methods="post")
     * @Validator(AccountValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function create()
    {
        $this->AccountService->create(Tools::Body());
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/account/{id}", methods="put")
     * @Validator(AccountValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function update(int $id)
    {
        $this->AccountService->update($id, Tools::Body());
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/account/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->AccountService->delete($id);
        return Tools::Ok();
    }
}
