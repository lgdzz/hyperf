<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Command\Command;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

class InitDataTableAction
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function handle(): bool
    {
        $this->importTable('dictionary');
        $this->importTable('file');
        $this->importTable('login_log');
        $this->importTable('oplog');
        $this->importTable('role');
        $this->importTable('rule');
        $this->importTable('setting');
        $this->importTable('user');
        $this->importTable('account');
        $this->importTable('organization');
        $this->importTable('organization_grade');
        return true;
    }

    private function importTable(string $table)
    {
        if (!Schema::hasTable($table)) {
            $sqlList = file_get_contents(__DIR__ . '/../sql/' . $table . '.sql');
            foreach (explode('=@=@=@=@=@=', $sqlList) as $sql) {
                Db::insert(trim($sql));
            }
            $this->command->line('导入[' . $table . ']表完成', 'info');
        } else {
            $this->command->line('导入[' . $table . ']表已存在', 'info');
        }
    }
}