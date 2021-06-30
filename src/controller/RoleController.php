<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\Factory;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\service\RoleService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class RoleController
{
    /**
     * @Inject
     * @var RoleService
     */
    protected $RoleService;

    /**
     * @RequestMapping(path="/l/role", methods="get")
     */
    public function index()
    {
        $result = $this->RoleService->index(Tools::Query());
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/role/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->RoleService->findById($id);
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/role", methods="post")
     */
    public function create()
    {
        $this->RoleService->create(Tools::Body());
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/role/{id}", methods="put")
     */
    public function update(int $id)
    {
        $this->RoleService->update($id, Tools::Body());
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/role/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->RoleService->delete($id);
        return Tools::Ok();
    }
}
