<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\hyperf\service\FileService;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 * })
 */
class FileController
{
    /**
     * @Inject
     * @var FileService
     */
    protected $FileService;

    /**
     * @RequestMapping(path="/l/file", methods="get")
     */
    public function index()
    {
        $result = $this->FileService->index(Tools::Query(['org_id' => Tools::Org()->id]));
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/file/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->FileService->file($this->FileService->findById($id));
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/file/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->FileService->delete($id);
        Tools::Oplog('文件删除[基础]');
        return Tools::Ok();
    }
}
