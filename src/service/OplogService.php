<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Oplog;
use lgdz\hyperf\Tools;
use lgdz\object\Query;

class OplogService
{
    public function index(Query $input)
    {
        $model = Oplog::query()->when($input->org_id, function ($query, $value) {
            return $query->where('org_id', $value);
        })->when($input->user_id, function ($query, $value) {
            return $query->where('user_id', $value);
        })->when($input->account_id, function ($query, $value) {
            return $query->where('account_id', $value);
        })->when($input->title, function ($query, $value) {
            return $query->where('title', 'like', '%' . $value . '%');
        })->when($input->method, function ($query, $value) {
            return $query->where('method', $value);
        })->orderByDesc('id');
        if ($input->is_page) {
            return Tools::P(
                $model->paginate($input->size)
            );
        } else {
            return $model->limit($input->limit ?: 10)->get();
        }
    }

    public static function create(string $title = '')
    {
        $request = Tools::I();
        $method = $request->getMethod();
        $data = [
            'org_id'     => Tools::Account()->org_id,
            'user_id'    => Tools::Account()->user_id,
            'account_id' => Tools::Account()->id,
            'title'      => $title,
            'operator'   => sprintf('[%s-%s]<登录账号:%s>', Tools::Org()->name, Tools::Account()->role->name, Tools::U()->username),
            'method'     => $method,
            'path'       => $request->getUri()->getPath(),
            'body'       => $method === 'GET' ? $request->getQueryParams() : $request->getParsedBody()
        ];
        go(function () use ($data) {
            $log = new Oplog();
            $log->org_id = $data['org_id'];
            $log->user_id = $data['user_id'];
            $log->account_id = $data['account_id'];
            $log->title = $data['title'];
            $log->operator = $data['operator'];
            $log->method = $data['method'];
            $log->path = $data['path'];
            $log->body = $data['body'];
            $log->save();
        });
    }
}