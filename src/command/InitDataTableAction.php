<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Command\Command;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use lgdz\hyperf\model\Role;
use lgdz\hyperf\model\Rule;
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
        $this->importTable('dictionary');
        $this->importTable('file');
        $this->importTable('login_log');
        $this->importTable('role');
        $this->importTable('rule');
        $this->importTable('setting');
        $this->importTable('user');

        $this->importData(new Role());
        $this->importData(new Rule());
        $this->importData(new User());
        return true;
    }

    private function importTable(string $table)
    {
        if (!Schema::hasTable($table)) {
            $sql = file_get_contents(__DIR__ . '/../sql/' . $table . '.sql');
            Db::insert($sql);
            $this->command->line('导入[' . $table . ']表完成', 'info');
        } else {
            $this->command->line('导入[' . $table . ']表已存在', 'info');
        }
    }

    private function importData($model)
    {
        $table = $model->getTable();
        $model->truncate();
        switch ($table) {
            case 'user':
                $model->initRootUser();
                break;
            default:
                $sql = file_get_contents(__DIR__ . '/../sql/' . $model->getTable() . '_insert.sql');
                Db::insert($sql);
                break;
        }
    }
}