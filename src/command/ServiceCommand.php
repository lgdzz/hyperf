<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\DbConnection\Db;
use Hyperf\Devtool\Generator\GeneratorCommand;
use lgdz\hyperf\Tools;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('lgdz:service');
        $this->setDescription('新增一个带增删改查服务的服务类')
            ->addArgument(...['model', InputArgument::REQUIRED, '数据模型，如：User']);
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stub/Service.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Service';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (($input->getOption('force') === false) && $this->alreadyExists($this->getNameInput())) {
            $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
            return 0;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        file_put_contents($path, $this->replaceModel($this->buildClass($name), $this->input->getArgument('model')));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));

        $this->openWithIde($path);

        return 0;
    }

    protected function replaceModel($stub, string $model)
    {
        $modelName = substr($model, strrpos($model, '\\') + 1);

        $database = config('databases.default.database');
        $table = Tools::F()->helper->snake($modelName);
        $list = Db::select("SELECT COLUMN_NAME name,COLUMN_COMMENT comment,COLUMN_DEFAULT def,DATA_TYPE type FROM INFORMATION_SCHEMA.COLUMNS where table_schema ='{$database}' AND table_name  = '{$table}' ");
        $string = '';
        foreach ($list as $index => $item) {
            if (in_array($item->name, ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            if ($item->def) {
                $default = $item->def;
            } else {
                $default = in_array($item->type, ['bigint', 'int', 'smallint', 'tinyint']) ? 0 : '\'\'';
            }
            $string .= '$item->' . $item->name . ' = $input->' . $item->name . ' ?: ' . $default . ' ;';
            if ($item->comment) {
                $string .= ' // ' . $item->comment;
            }
            $string .= "\n        ";
        }
        $string = rtrim($string, "\n        ");

        return str_replace(
            ['%MODEL%', '%MODEL_NAME%', '%SET_FORM_DATA%'],
            [$model, $modelName, $string],
            $stub
        );
    }
}
