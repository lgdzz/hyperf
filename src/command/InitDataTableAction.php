<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Command\Command;
use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\DbConnection\Db;
use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\User;

class InitDataTableAction
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function handle(): bool
    {
        $this->import('dictionary');
        $this->import('file');
        $this->import('login_log');
        $this->import('role');
        $this->import('rule');
        $this->import('setting');
        $this->import('user');
        return true;
    }

    private function import(string $table)
    {
        if (!Schema::hasTable($table)) {
            $sql = file_get_contents(__DIR__ . '/../sql/' . $table . '.sql');
            Db::insert($sql);
            $this->command->line('导入[' . $table . ']表完成', 'info');
            switch ($table) {
                case 'user':
                    (new User())->initRootUser();
                    break;
                case 'role':
                    (new Role())->initRootRole();
                    break;
            }
        } else {
            $this->command->line('导入[' . $table . ']表已存在', 'info');
        }
    }
}