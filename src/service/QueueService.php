<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\JobInterface;

/**
 * Class QueueService
 * @package App\Service
 */
class QueueService
{
    // 消费失败的队列
    const QUEUE_FAILED = 'failed';
    // 消费超时的队列 (虽然超时，但可能执行成功)
    const QUEUE_TIMEOUT = 'timeout';

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * QueueService constructor.
     * @param DriverFactory $driverFactory
     */
    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 生产消息.
     * @param JobInterface $job
     * @param int $delay 延时时间 单位秒
     * @return bool
     */
    public function push(JobInterface $job, int $delay = 0): bool
    {
        // 这里的 `ExampleJob` 会被序列化存到 Redis 中，所以内部变量最好只传入普通数据
        // 同理，如果内部使用了注解 @Value 会把对应对象一起序列化，导致消息体变大。
        // 所以这里也不推荐使用 `make` 方法来创建 `Job` 对象。
        return $this->driver->push($job, $delay);
    }

    /**
     * 重新加载失败的消息队列
     * @param string $queue
     * @return int
     */
    public function reload(string $queue): int
    {
        return $this->driver->reload($queue);
    }

    /**
     * 消息队列统计信息
     * @return array
     */
    public function info(): array
    {
        return $this->driver->info();
    }

    /**
     * 删除延迟消息队列任务
     * @param JobInterface $job
     * @return bool
     */
    public function delete(JobInterface $job): bool
    {
        return $this->driver->delete($job);
    }
}