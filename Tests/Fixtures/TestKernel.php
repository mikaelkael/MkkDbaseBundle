<?php

namespace Mkk\DbaseBundle\Tests\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Used for functional tests.
 */
class TestKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Mkk\DbaseBundle\MkkDbaseBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/Tests/Fixtures/cache/'.$this->environment;
    }
}

class_alias('Mkk\DbaseBundle\Tests\Fixtures\TestKernel', 'TestKernel');
