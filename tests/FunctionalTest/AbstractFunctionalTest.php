<?php


namespace DI\Bridge\Symfony\Test\FunctionalTest;

use DI\Bridge\Symfony\Test\FunctionalTest\Fixtures\AppKernel;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

abstract class AbstractFunctionalTest extends TestCase
{
    protected function createKernel($configFile = 'empty.yml')
    {
        // Clear the cache
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/Fixtures/cache/dev');

        $kernel = new AppKernel($configFile);
        $kernel->boot();

        return $kernel;
    }
}
