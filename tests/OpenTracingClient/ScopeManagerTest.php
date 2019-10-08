<?php

namespace Tests\OpenTracingClient;

use OpenTracingClient\ScopeManager;
use OpenTracingClient\Tracer;
use PHPUnit\Framework\TestCase;

final class ScopeManagerTest extends TestCase
{
    public function testGetActiveFailsWithNoActiveSpans(): void
    {
        $scopeManager = new ScopeManager();

        $this->assertNull($scopeManager->getActive());
    }

    public function testActivateSuccess(): void
    {
        $tracer = new Tracer();
        $span = $tracer->startSpan('name');
        $scopeManager = new ScopeManager();
        $scopeManager->activate($span);

        $this->assertSame($span, $scopeManager->getActive()->getSpan());
    }

    public function testGetScopeReturnsNull(): void
    {
        $tracer = new Tracer();
        $tracer->startSpan('name');
        $scopeManager = new ScopeManager();

        $this->assertNull($scopeManager->getActive());
    }

    public function testGetScopeSuccess(): void
    {
        $tracer = new Tracer();
        $span = $tracer->startSpan('name');
        $scopeManager = new ScopeManager();
        $scopeManager->activate($span);
        $scope = $scopeManager->getActive();

        $this->assertSame($span, $scope->getSpan());
    }

    public function testDeactivateSuccess(): void
    {
        $tracer = new Tracer();
        $span = $tracer->startSpan('name');
        $scopeManager = new ScopeManager();
        $scopeManager->activate($span);
        $scope = $scopeManager->getActive();
        $scopeManager->deactivate($scope);

        $this->assertNull($scopeManager->getActive());
    }
}
