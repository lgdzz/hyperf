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
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\model\Account;
use lgdz\hyperf\service\AccountService;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\validator\AccountValidator;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
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
        $result = $this->AccountService->index(Tools::Query(), ['role', 'org', 'user'], function (Account $account) {
            $item = $account->toArray();
            if ($account->user->from_channel === '组织' && $account->user->from_id === Tools::Org()->id) {
                $item['updateUser'] = true;
            } else {
                $item['updateUser'] = false;
            }
            return $item;
        });
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/account/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->AccountService->account($this->AccountService->findById($id, ['org', 'user']));
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
        Tools::Oplog('添加组织用户[基础]');
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
        Tools::Oplog('修改组织用户[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/account/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->AccountService->delete($id);
        Tools::Oplog('删除组织用户[基础]');
        return Tools::Ok();
    }
}
