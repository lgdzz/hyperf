<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use EasyWeChat\Factory;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractWechatService
{
    protected $container = [];
    protected $cache;

    /**
     * 缓存
     * @return mixed|CacheInterface
     */
    protected function cache()
    {
        if (is_null($this->cache)) {
            $this->cache = ApplicationContext::getContainer()->get(CacheInterface::class);
        }
        return $this->cache;
    }

    /**
     * 企业微信操作对象
     * @param array $config
     * @return \EasyWeChat\Work\Application|mixed|null
     */
    public function workApp(array $config)
    {
        $corp_id = $config['corp_id'];
        $app = $this->container[$corp_id] ?? null;
        if (is_null($app)) {
            $app = Factory::work($config);
            $app['guzzle_handler'] = CoroutineHandler::class;
            $app->rebind('cache', $this->cache());
            $this->container[$corp_id] = $app;
        }
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
        $app = $this->container[$corp_id] ?? null;
        if (is_null($app)) {
            $app = Factory::miniProgram($config);
            $app['guzzle_handler'] = CoroutineHandler::class;
            $app->rebind('cache', $this->cache());
            $this->container[$corp_id] = $app;
        }
        return $app;
    }
}