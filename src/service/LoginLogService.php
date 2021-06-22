<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use App\Model\LoginLog;
use App\Model\User;
use App\Utils\Tools;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class LoginLogService
{
    public function index(array $input)
    {
        $ip = $input['ip'] ?? false;
        $user_id = $input['user_id'] ?? false;
        $page = $input['page'] ?? false;
        $page_size = $input['page_size'] ?? 15;
        $model = LoginLog::query()->when($user_id, function ($query, $value) {
            return $query->where('user_id', $value);
        })->orderByDesc('id');
        if ($page) {
            return Tools::P(
                $model->paginate((int)$page_size)
            );
        } else {
            return $model->limit($input['limit'] ?? 10)->get();
        }
    }

    public static function create(int $user_id, string $channel)
    {
        $request = Context::get(ServerRequestInterface::class);
        $client_ip = $request->getHeader('x-real-ip')[0] ?? 'myip';
        go(function () use ($user_id, $channel, $client_ip) {
            $ip_info = Tools::$factory->Helper()->getIpInfo($client_ip);
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
                $user->last_time = time();
                $user->save();
            }
        });
    }
}