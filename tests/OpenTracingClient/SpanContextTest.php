<?php

namespace Tests\OpenTracingClient;

use OpenTracingClient\SpanContext;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_a_span_context(): void
    {
        $spanContext = SpanContext::create( 'trace_id', 'span_id', 'parent_id', true, ['key' => 'value'] );

        $this->assertEquals('trace_id', $spanContext->getTraceId());
        $this->assertEquals('span_id', $spanContext->getSpanId());
        $this->assertEquals('parent_id', $spanContext->getParentId());
        $this->assertEquals(true, $spanContext->isSampled());
        $this->assertEquals(['key' => 'value'], iterator_to_array($spanContext));
        $this->assertEquals('value', $spanContext->getBaggageItem('key'));
    }

    /**
     * @test
     */
    public function it_can_create_parent_and_child_contexts(): void
    {
        $parentContext = SpanContext::createAsRoot();
        $childContext = SpanContext::createAsChildOf($parentContext);

        $this->assertEquals($parentContext->getTraceId(), $childContext->getTraceId());
        $this->assertEquals(null, $parentContext->getParentId());
        $this->assertEquals($parentContext->getSpanId(), $childContext->getParentId());
    }

    /**
     * @test
     */
    public function it_can_add_context_with_baggage(): void
    {
        $spanContext = SpanContext::create( 'test_trace_id', 'test_span_id', true );
        $this->assertEmpty(iterator_to_array($spanContext));

        $spanContext = $spanContext->withBaggageItem('test_key', 'test_value');
        $this->assertEquals(['test_key' => 'test_value'], iterator_to_array($spanContext));
    }
}
