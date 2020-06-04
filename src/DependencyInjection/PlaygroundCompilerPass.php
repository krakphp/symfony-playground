<?php

namespace Krak\SymfonyPlayground\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\TypedReference;

class PlaygroundCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) {
        $projectDir = $container->getParameter("kernel.project_dir");
        $reflectionFn = self::loadPlaygroundCallableReflection($projectDir . '/playground.php');
        if (!$reflectionFn) {
            $container->removeDefinition("krak.symfony_playground.playground_command");
            return;
        }

        $args = [];
        foreach ($reflectionFn->getParameters() as $param) {
            $type = $target = ProxyHelper::getTypeHint($reflectionFn, $param, true);
            $args[$param->name] = $type ? new TypedReference($target, $type) : new Reference($target);
        }

        $container->getDefinition("krak.symfony_playground.playground_command")->replaceArgument(0, ServiceLocatorTagPass::register($container, $args));
    }

    public static function loadPlaygroundCallableReflection(string $path): ?\ReflectionFunction {
        $playgroundFn = @include $path;
        if (!$playgroundFn) {
            return null;
        }

        if (!is_callable($playgroundFn)) {
            throw new \RuntimeException('playground.php must return a function.');
        }

        return new \ReflectionFunction($playgroundFn);
    }
}
