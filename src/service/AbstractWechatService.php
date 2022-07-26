<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use EasyWeChat\Factory;
use Hyperf\Guzzle\CoroutineHandler;
use lgdz\hyperf\Tools;

abstract class AbstractWechatService
{
    /**
     * 企业微信操作对象
     * @param array $config
     * @return \EasyWeChat\Work\Application|mixed|null
     */
    public function workApp(array $config)
    {
        $corp_id = $config['corp_id'];
        $app = Factory::work($config);
        $app['guzzle_handler'] = CoroutineHandler::class;
        $app->rebind('cache', Tools::C());
        return $app;
    }

    /**
     * 微信小程序操作对象
     * @param array $config
     * @return \EasyWeChat\MiniProgram\Application|mixed|null
     */
    public function miniApp(array $config)
    {
        $corp_id = $config['corp_id'];
        $app = Factory::miniProgram($config);
        $app['guzzle_handler'] = CoroutineHandler::class;
        $app->rebind('cache', Tools::C());
        return $app;
    }
}