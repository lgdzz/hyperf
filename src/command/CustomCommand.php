<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class CustomCommand extends Command
{
    protected $name = 'lgdz:cmd';

    public $commands = [
        'init_db'     => InitDataTableAction::class,
        'clear_cache' => ClearCacheAction::class
        // 其他指令处理类
        // ...
    ];

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Custom Command');
    }

    public function handle()
    {
        $action = $this->input->getArgument('action');
        if (is_null($action)) {
            $this->line('请输入指令', 'error');
            return;
        }
        $class = $this->commands[$action] ?? null;
        if (is_null($class)) {
            $this->line(sprintf('指令[%s]不存在', $action), 'error');
            return;
        }
        if ((new $class($this))->handle()) {
            $this->line('执行脚本成功-Success', 'info');
            return;
        } else {
            $this->line('执行脚本失败-Fail', 'info');
            return;
        }
    }

    protected function getArguments()
    {
        return [
            ['action', InputArgument::OPTIONAL, '指令方法']
        ];
    }
}
