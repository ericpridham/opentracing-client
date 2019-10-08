<?php

namespace Tests\OpenTracingClient;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use OpenTracingClient\Span;
use OpenTracingClient\SpanContext;
use PHPUnit\Framework\TestCase;

/**
 * @covers Span
 */
class SpanTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_a_basic_span(): void
    {
        $startTime = Carbon::now();
        $context = SpanContext::createAsRoot();
        $span = new Span('name', $context, $startTime);

        $this->assertEquals($startTime, $span->getStartTime());
        $this->assertEquals($context->getSpanId(), $span->getContext()->getSpanId());
        $this->assertEmpty($span->getTags());
        $this->assertEmpty($span->getLogs());
    }

    /**
     * @test
     */
    public function it_can_add_tags_and_logs(): void
    {
        $span = new Span('name', SpanContext::createAsRoot());

        $span->setTag('key', 'value');
        $span->log(['log']);

        $this->assertEquals(['key' => 'value'], $span->getTags());
        $this->assertEquals('log', $span->getLogs()[0]['fields'][0]);
    }

    /**
     * @test
     */
    public function it_can_finish(): void
    {
        $startTime = CarbonImmutable::now();
        $span = new Span('name', SpanContext::createAsRoot(), $startTime);
        $span->finish($startTime->addMilliseconds(100));

        $this->assertTrue($span->isFinished());
        $this->assertEquals(100, $span->getDurationMs());
    }
}
