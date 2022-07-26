<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\validator\DictionaryValidator;
use lgdz\hyperf\service\DictionaryService;
use lgdz\hyperf\Tools;

/**
 * 字典管理
 * Class DictionaryController
 * @package lgdz\hyperf\controller
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class DictionaryController
{
    /**
     * @Inject()
     * @var DictionaryService
     */
    protected $DictionaryService;

    /**
     * 字典列表(树结构)
     * @RequestMapping(path="/l/dictionary", methods="get")
     */
    public function index()
    {
        $result = $this->DictionaryService->index();
        return Tools::Ok($result);
    }

    /**
     * 字典详情
     * @RequestMapping(path="/l/dictionary/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->DictionaryService->dictionary($this->DictionaryService->findById($id));
        return Tools::Ok($result);
    }

    /**
     * 字典添加
     * @RequestMapping(path="/l/dictionary", methods="post")
     * @Middleware(ValidatorMiddleware::class)
     * @Validator(DictionaryValidator::class)
     */
    public function create()
    {
        $this->DictionaryService->create(Tools::Body());
        Tools::Oplog('添加字典[基础]');
        return Tools::Ok();
    }

    /**
     * 字典更新
     * @RequestMapping(path="/l/dictionary/{id}", methods="put")
     * @Middleware(ValidatorMiddleware::class)
     * @Validator(DictionaryValidator::class)
     */
    public function update(int $id)
    {
        $this->DictionaryService->update($id, Tools::Body());
        Tools::Oplog('修改字典[基础]');
        return Tools::Ok();
    }

    /**
     * 字典删除
     * @RequestMapping(path="/l/dictionary/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->DictionaryService->delete($id);
        Tools::Oplog('删除字典[基础]');
        return Tools::Ok();
    }
}