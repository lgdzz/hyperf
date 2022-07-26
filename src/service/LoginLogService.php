<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\LoginLog;
use lgdz\hyperf\model\User;
use lgdz\hyperf\Tools;
use lgdz\Factory;
use lgdz\object\Query;

class LoginLogService
{
    public function index(Query $input)
    {
        $model = LoginLog::query()->when($input->user_id, function ($query, $value) {
            return $query->where('user_id', $value);
        })->orderByDesc('id');
        if ($input->is_page) {
            return Tools::P(
                $model->paginate($input->size)
            );
        } else {
            return $model->limit($input->limit ?: 10)->get();
        }
    }

    public static function create(int $user_id, string $channel)
    {
        $request = Tools::I();
        $client_ip = $request->getHeader('x-real-ip')[0] ?? 'myip';
        go(function () use ($user_id, $channel, $client_ip) {
            $ip_info = Factory::container()->helper->getIpInfo($client_ip);
            $model = new LoginLog();
            $model->user_id = $user_id;
            $model->ip_isp = $ip_info['isp'];
            $model->login_ip = $ip_info['ip'];
            $model->login_time = date('Y年m月d日 H:i');
            $model->channel = $channel;
            $model->save();
            $user = User::query()->where('id', $user_id)->first();
            if ($user instanceof User) {
                $user->last_ip = $model->login_ip;
                $user->last_time = date('Y-m-d H:i:s');
                $user->save();
            }
        });
    }
}