<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConfigurationTest extends WebTestCase
{
    public function testFrameworkTestConfigurationIsEnabled(): void
    {
        // Create a kernel and boot it with test environment
        $kernel = static::createKernel(['environment' => 'test']);
        $kernel->boot();
        
        // Get the container
        $container = $kernel->getContainer();
        
        // Check if the test configuration is enabled
        $this->assertTrue($container->getParameter('kernel.debug'));
        $this->assertEquals('test', $container->getParameter('kernel.environment'));
        
        // Try to create a client
        $client = static::createClient(['environment' => 'test']);
        $this->assertNotNull($client);
    }
}