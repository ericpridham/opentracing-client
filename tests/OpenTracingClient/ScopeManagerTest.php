<?php

namespace Tests\OpenTracingClient;

use OpenTracingClient\ScopeManager;
use OpenTracingClient\Tracer;
use PHPUnit\Framework\TestCase;

class ScopeManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_null_if_no_active_spans(): void
    {
        $scopeManager = new ScopeManager();

        $this->assertNull($scopeManager->getActive());
    }

    /**
     * @test
     */
    public function it_can_activate_a_span(): void
    {
        $tracer = new Tracer();
        $span = $tracer->startSpan('name');
        $scopeManager = new ScopeManager();
        $scopeManager->activate($span);

        $this->assertSame($span, $scopeManager->getActive()->getSpan());
    }

    /**
     * @test
     */
    public function it_automatically_deactivates_finished_spans(): void
    {
        $tracer = new Tracer();
        $scopeManager = new ScopeManager();

        $scope1 = $scopeManager->activate($tracer->startSpan('name1'));
        $scope2 = $scopeManager->activate($tracer->startSpan('name2'));

        $this->assertSame($scope2, $scopeManager->getActive());

        $scope2->close();

        $this->assertSame($scope1, $scopeManager->getActive());

        $scope1->close();

        $this->assertNull($scopeManager->getActive());
    }
}
