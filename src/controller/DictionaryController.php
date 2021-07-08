<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use lgdz\hyperf\service\DictionaryService;
use lgdz\hyperf\Tools;

class DictionaryController
{
    /**
     * @Inject()
     * @var DictionaryService
     */
    protected $DictionaryService;

    public function index()
    {
        $result = $this->DictionaryService->index();
        return Tools::Ok($result);
    }

    public function read(int $id, ResponseInterface $response)
    {
        $model = $this->DictionaryService->read($id);
        if ($model->pid > 0) {
            $model->parent = $this->DictionaryService->read($model->pid);
        } else {
            $model->parent = ['id' => 0, 'description' => '根字典'];
        }
        return $response->json(Tools::R()->ok($model));
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        $input = $request->getParsedBody();
        $this->DictionaryService->create($input);
        return $response->json(Tools::R(sprintf('添加字典[NAME:%s]', $input['name']))->ok());
    }

    public function update(int $id, RequestInterface $request, ResponseInterface $response)
    {
        $this->DictionaryService->update($id, $request->getParsedBody());
        return $response->json(Tools::R(sprintf('修改字典[ID:%s]', $id))->ok());
    }

    public function delete(int $id, ResponseInterface $response)
    {
        $this->DictionaryService->delete($id);
        return $response->json(Tools::R(sprintf('删除字典[ID:%s]', $id))->ok());
    }
}