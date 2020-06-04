<?php

namespace Krak\SymfonyPlayground\Tests\Feature;

use Krak\SymfonyPlayground\PlaygroundBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\HttpKernel\Kernel;

final class PlaygroundTest extends BaseBundleTestCase
{
    /**
     * @var TestKernel
     */
    private $kernel;
    private $cachePrefix;
    /** @var ?\Throwable */
    private $exception;

    protected function setUp(): void {
        $this->given_the_playground_php_file_is_removed();
        $this->cachePrefix = uniqid('cache');
    }

    protected function tearDown(): void {
        if ($this->kernel) {
            $this->kernel->shutdown();
            shell_exec("rm -rf {$this->kernel->getCacheDir()}");
        }
        $this->given_the_playground_php_file_is_removed();
    }

    protected function createKernel() {
        $this->kernel = new TestKernel($this->cachePrefix);
        $this->kernel->addBundle($this->getBundleClass());
        $this->kernel->setProjectDir(__DIR__ . '/Fixtures');
        $this->kernel->addCompilerPasses([new PublicServicePass('/krak.symfony_playground.playground_command/')]);
        return $this->kernel;
    }

    protected function getBundleClass() {
        return PlaygroundBundle::class;
    }

    public function test_does_nothing_if_no_playground_file_exists() {
        $this->when_the_kernel_is_booted();
        $this->then_no_exceptions_should_be_thrown();
        $this->then_the_container_should_be_built(1);
        $this->then_the_service_does_not_exist('krak.symfony_playground.playground_command');
    }

    public function test_rebuilds_sf_di_cache_on_first_invoke() {
        $this->given_the_playground_php_file_is_set_with();
        $this->when_the_kernel_is_booted();
        $this->then_the_container_should_be_built(1);
    }

    public function test_does_not_rebuild_sf_di_cache_on_second_invoke() {
        $this->given_the_playground_php_file_is_set_with('Psr\Log\LoggerInterface $logger');
        $this->given_the_kernel_is_booted_and_waits();
        $this->when_the_kernel_is_rebooted();
        $this->then_the_container_should_be_built(1);
    }

    public function test_does_not_rebuild_sf_di_cache_on_file_update() {
        $this->given_the_playground_php_file_is_set_with();
        $this->given_the_kernel_is_booted_and_waits();
        $this->given_the_playground_php_file_is_set_with();
        $this->when_the_kernel_is_rebooted();
        $this->then_the_container_should_be_built(1);
    }

    public function test_rebuilds_sf_di_cache_if_playground_params_change() {
        $this->given_the_playground_php_file_is_set_with('Psr\Log\LoggerInterface $logger');
        $this->given_the_kernel_is_booted_and_waits();
        $this->given_the_playground_php_file_is_set_with('Psr\Log\LoggerInterface $logger1');
        $this->when_the_kernel_is_rebooted();
        $this->then_the_container_should_be_built(2);
    }

    public function test_allows_playground_files_with_no_args() {
        $this->given_the_playground_php_file_is_set_with('');
        $this->when_the_kernel_is_booted();
        $this->then_no_exceptions_should_be_thrown();
    }

    private function given_the_kernel_is_booted_and_waits(?int $seconds = 1) {
        $this->when_the_kernel_is_booted();
        if ($seconds) {
            sleep($seconds);
        }
    }

    private function given_the_system_sleeps_for(string $seconds) {
        sleep($seconds);
    }

    private function given_the_playground_php_file_is_removed() {
        @unlink(__DIR__ . '/Fixtures/playground.php');
    }

    private function given_the_playground_php_file_is_reset_after(int $seconds, string $args = 'Psr\Log\LoggerInterface $logger') {

    }
    private function given_the_playground_php_file_is_set_with(string $args = 'Psr\Log\LoggerInterface $logger') {
        file_put_contents(__DIR__ . '/Fixtures/playground.php', <<<PHP
<?php
return function($args) {};
PHP
        );
    }

    private function when_the_kernel_is_booted() {
        try {
            $this->createKernel();
            $this->kernel->boot();
        } catch (\Throwable $e) {dump($e);
            $this->exception = $e;
        }
    }

    private function when_the_kernel_is_rebooted(int $nTimes = 1) {
        foreach (range(1, $nTimes) as $i) {
            $this->kernel->reboot($this->kernel->getCacheDir());
        }
    }

    private function then_no_exceptions_should_be_thrown() {
        $this->assertEquals(null, $this->exception);
    }

    private function then_the_container_should_be_built(int $times) {
        $this->assertEquals($times, $this->kernel->containerBuildCount);
    }

    private function then_the_service_does_not_exist(string $id) {
        $this->assertFalse($this->kernel->getContainer()->has($id));
    }
}
