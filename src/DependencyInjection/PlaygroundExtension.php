<?php

namespace Krak\SymfonyPlayground\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Resource\FileResource;

final class PlaygroundExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) {
        $projectDir = $container->getParameter("kernel.project_dir");
        $container->addResource(new PlaygroundFileResource($projectDir . "/playground.php"));
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
