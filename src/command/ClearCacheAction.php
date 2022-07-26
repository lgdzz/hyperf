<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Command\Command;
use Hyperf\Config\Annotation\Value;
use Hyperf\Redis\Redis;
use lgdz\hyperf\Tools;

class ClearCacheAction
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function handle(): bool
    {
        $redis = Tools::container()->get(Redis::class);
        $keys = $redis->keys('mc:default:*');
        $result = empty($keys) ? 0 : $redis->del(...$keys);
        $this->command->line("成功删除{$result}条记录", 'info');
        return true;
    }
}