<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use App\Request\UserRequest;
use App\Service\UserService;
use App\Utils\Tools;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class UserController
{
    /**
     * @Inject
     * @var UserService
     */
    protected $UserService;

    // 用户列表
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $result = $this->UserService->index(array_merge(
            $request->getQueryParams(),
            ['agency_id' => Tools::U()->agency_id]
        ));
        return $response->json(Tools::R()->ok($result));
    }

    // 用户详情
    public function read(int $id, RequestInterface $request, ResponseInterface $response)
    {
        $result = $this->UserService->read($id, $request->getAttribute('agency_id', 0));
        // 将org_id转成表单需要的数组结构
        $org_id = $result->org->path;
        $result = $result->toArray();
        $result['org_id'] = $org_id;
        return $response->json(Tools::R()->ok($result));
    }

    // 创建新用户
    public function create(UserRequest $request, ResponseInterface $response)
    {
        $this->UserService->create($request->getParsedBody());
        return $response->json(Tools::R()->ok());
    }

    // 编辑用户
    public function update(int $id, UserRequest $request, ResponseInterface $response)
    {
        $this->UserService->update($id, $request->getParsedBody());
        return $response->json(Tools::R()->ok());
    }

    // 删除用户
    public function delete(int $id, ResponseInterface $response)
    {
        $this->UserService->delete($id);
        return $response->json(Tools::R()->ok());
    }

}
