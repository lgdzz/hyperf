<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\Factory;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\service\RuleService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class RuleController
{
    /**
     * @Inject
     * @var RuleService
     */
    protected $RuleService;

    /**
     * @RequestMapping(path="/l/rule", methods="get")
     */
    public function index()
    {
        $result = $this->RuleService->index(Tools::Query());
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/rule/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->RuleService->rule($this->RuleService->findById($id));
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/rule", methods="post")
     */
    public function create()
    {
        $this->RuleService->create(Tools::Body());
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/rule/{id}", methods="put")
     */
    public function update(int $id)
    {
        $this->RuleService->update($id, Tools::Body());
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/rule/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->RuleService->delete($id);
        return Tools::Ok();
    }
}
