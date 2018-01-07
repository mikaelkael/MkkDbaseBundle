<?php

namespace Mkk\DbaseBundle\Tests\Service;

use Mkk\DbaseBundle\Component\Dbase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DbaseTest extends WebTestCase
{

    public function testService()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $service = $container->get('mkk_dbase.dbase');
        $this->assertTrue($service instanceof Dbase);
    }
}