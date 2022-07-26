<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\service\OplogService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class OplogController
{
    /**
     * @Inject
     * @var OplogService
     */
    protected $service;

    /**
     * @RequestMapping(path="/l/oplog", methods="get")
     */
    public function index()
    {
        $result = $this->service->index(Tools::Query(['org_id' => Tools::Org()->id]));
        return Tools::Ok($result);
    }
}
