<?php

namespace Krak\SymfonyPlayground\Tests\Feature;

use Nyholm\BundleTest\AppKernel;

/**
 * Test kernel used to help identify when the container is built to test for performance issues
 */
final class TestKernel extends AppKernel
{
    public $containerBuildCount = 0;
    protected function buildContainer() {
        $this->containerBuildCount += 1;
        return parent::buildContainer(); // TODO: Change the autogenerated stub
    }

    public function getCacheDir() {
        return $this->getProjectDir() . '/cache';
    }
}
