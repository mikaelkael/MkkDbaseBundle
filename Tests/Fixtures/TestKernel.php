<?php

namespace Mkk\DbaseBundle\Tests\Fixtures;

use Mkk\DbaseBundle\MkkDbaseBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Used for functional tests.
 */
class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [new FrameworkBundle(), new MkkDbaseBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/Tests/Fixtures/cache/'.$this->environment;
    }
}

class_alias('Mkk\DbaseBundle\Tests\Fixtures\TestKernel', 'TestKernel');
