<?php

declare(strict_types=1);

namespace lgdz\hyperf\command;

use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('lgdz:controller');
        $this->setDescription('新增一个带增删改查接口的控制器')
            ->addArgument(...['route', InputArgument::REQUIRED, '接口路由地址，如：/test'])
            ->addArgument(...['service', InputArgument::REQUIRED, '服务类']);
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stub/Controller.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Controller';
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

        file_put_contents($path, $this->replaceRoute($this->buildClass($name), $this->input->getArgument('route'), $this->input->getArgument('service')));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));

        $this->openWithIde($path);

        return 0;
    }

    protected function replaceRoute($stub, string $route, string $service)
    {
        $serviceName = substr($service, strrpos($service, '\\') + 1);

        return str_replace(
            ['%ROUTE%', '%SERVICE%', '%SERVICE_NAME%'],
            [$route, $service, $serviceName],
            $stub
        );
    }
}
