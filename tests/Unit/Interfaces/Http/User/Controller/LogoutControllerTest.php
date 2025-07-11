<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http\User\Controller;

use App\Interfaces\Http\User\Controller\LogoutController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogoutControllerTest extends TestCase
{
    private LogoutController $controller;

    protected function setUp(): void
    {
        $this->controller = new LogoutController();
    }

    public function testInvokeReturnsSuccessMessage(): void
    {
        // Act
        $response = $this->controller->__invoke();

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertEquals('Successfully logged out', $content['message']);
        $this->assertEquals('success', $content['status']);
    }

    public function testInvokeAlwaysReturnsTheSameResponse(): void
    {
        // Act
        $response1 = $this->controller->__invoke();
        $response2 = $this->controller->__invoke();

        // Assert
        $this->assertEquals($response1->getStatusCode(), $response2->getStatusCode());
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    public function testInvokeResponseIsProperJson(): void
    {
        // Act
        $response = $this->controller->__invoke();

        // Assert
        $content = $response->getContent();
        $this->assertJson($content);
        
        $decodedContent = json_decode($content, true);
        $this->assertIsArray($decodedContent);
        $this->assertCount(2, $decodedContent);
    }

    public function testInvokeResponseHasCorrectHeaders(): void
    {
        // Act
        $response = $this->controller->__invoke();

        // Assert
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
    }

    public function testControllerImplementsInvokeMethodOnly(): void
    {
        $reflection = new \ReflectionClass(LogoutController::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $publicMethods);
        
        // Should only have __invoke public method (no constructor in this case)
        $this->assertContains('__invoke', $methodNames);
        
        // Should exclude inherited methods from AbstractController
        $ownMethods = array_filter($publicMethods, function($method) {
            return $method->getDeclaringClass()->getName() === LogoutController::class;
        });
        
        // Count should be exactly 1 (only invoke)
        $this->assertCount(1, $ownMethods);
    }
}