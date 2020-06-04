<?php

namespace Krak\SymfonyPlayground\DependencyInjection;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

final class PlaygroundFileResource implements SelfCheckingResourceInterface
{
    private $path;
    private $uniqueKey;

    public function __construct(string $path) {
        $this->path = $path;
        $this->uniqueKey = $this->calculateUniqueKeyFromPlaygroundFnParams($path);
    }

    public function __toString() {
        return 'playground.'.$this->path;
    }

    public function isFresh($timestamp) {
        $currentUniqueKey = $this->calculateUniqueKeyFromPlaygroundFnParams($this->path);
        return $this->uniqueKey === $currentUniqueKey;
    }

    private function calculateUniqueKeyFromPlaygroundFnParams(string $path): ?string {
        if (!file_exists($path)) {
            return null;
        }

        $reflectionFn = PlaygroundCompilerPass::loadPlaygroundCallableReflection($this->path);
        if (!$reflectionFn) {
            throw new \RuntimeException('Expected playground.php file to return a function.');
        }

        return implode(',', array_map(function(\ReflectionParameter $parameter) {
            return (string) $parameter;
        }, $reflectionFn->getParameters()));
    }
}
