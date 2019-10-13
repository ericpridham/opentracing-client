<?php

namespace Tests\OpenTracingClient;

use Mockery;
use OpenTracingClient\Scope;
use OpenTracingClient\Span;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    /** @test */
    public function it_can_be_closed(): void
    {
        $span = Mockery::spy(Span::class);

        $scope = new Scope($span, true);
        $scope->close();

        $this->assertTrue($scope->isClosed());
        $span->shouldHaveReceived('finish');
    }

    /** @test */
    public function it_can_keep_a_span_open_on_close(): void
    {
        $span = Mockery::spy(Span::class);

        $scope = new Scope($span, false);
        $scope->close();

        $this->assertTrue($scope->isClosed());
        $span->shouldNotHaveReceived('finish');
    }
}