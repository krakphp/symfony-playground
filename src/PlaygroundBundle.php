<?php

namespace Krak\SymfonyPlayground;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PlaygroundBundle extends Bundle
{
    const VERSION = '0.1.0';

    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new DependencyInjection\PlaygroundCompilerPass());
    }
}
