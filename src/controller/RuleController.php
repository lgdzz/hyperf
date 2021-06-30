<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use App\Request\RuleRequest;
use App\Service\RuleService;
use App\Utils\Tools;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class RuleController
{
    /**
     * @Inject
     * @var RuleService
     */
    protected $RuleService;

    // 权限规则列表
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $result = $this->RuleService->index([]);
        $result = Tools::$factory->Tree()->tree($result->toArray(), 0);
        return $response->json(Tools::R()->ok($result));
    }

    // 权限规则详情
    public function read(int $id, ResponseInterface $response)
    {
        $result = $this->RuleService->read($id);
        return $response->json(Tools::R()->ok($result));
    }

    // 创建新规则
    public function create(RuleRequest $request, ResponseInterface $response)
    {
        $input = $request->getParsedBody();
        $this->RuleService->create($input);
        return $response->json(Tools::R(sprintf('创建权限规则[NAME:%s]', $input['name']))->ok());
    }

    // 编辑规则
    public function update(int $id, RuleRequest $request, ResponseInterface $response)
    {
        $this->RuleService->update($id, $request->getParsedBody());
        return $response->json(Tools::R(sprintf('修改权限规则[ID:%s]', $id))->ok());
    }

    // 删除规则
    public function delete(int $id, ResponseInterface $response)
    {
        $this->RuleService->delete($id);
        return $response->json(Tools::R(sprintf('删除权限规则[ID:%s]', $id))->ok());
    }
}
