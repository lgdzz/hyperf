<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Command\Command;
use lgdz\hyperf\Tools;

class DataDocAction
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function handle(): bool
    {
        $db = Tools::F()->db_dictionary;
        $db->setDbname(env('DB_DATABASE'));
        $db->setHost(env('DB_HOST'));
        $db->setUsername(env('DB_USERNAME'));
        $db->setPassword(env('DB_PASSWORD'));
        $db->setPort(env('DB_PORT'));
        $db->setProjectName(env('PROJECT_NAME'));
        $db->build();
        $this->command->line("数据库字典生成完毕。", 'info');
        return true;
    }
}