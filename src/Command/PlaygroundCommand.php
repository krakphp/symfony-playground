<?php

namespace Krak\SymfonyPlayground\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;

class PlaygroundCommand extends Command
{
    private $container;
    private $projectDir;

    public function __construct(ContainerInterface $container, string $projectDir) {
        $this->container = $container;
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('playground')
            ->setDescription("Execute the playground.php file");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $playgroundFn = @include $this->projectDir . '/playground.php';
        if (!$playgroundFn) {
            $output->writeln("No playground fn to execute.");
            return;
        }

        $rf = new \ReflectionFunction($playgroundFn);
        $params = array_map(function($param) {
            return $this->container->get($param->name);
        }, $rf->getParameters());
        $playgroundFn(...$params);
    }
}
