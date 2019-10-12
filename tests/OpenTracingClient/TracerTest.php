<?php

namespace Tests\OpenTracingClient;

use Mockery;
use OpenTracing\Exceptions\UnsupportedFormat;
use OpenTracing\NoopSpan;
use OpenTracing\Span as OTSpan;
use OpenTracingClient\Tracer;
use OpenTracingClient\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Tracer
 */
class TracerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_start_an_active_span(): void
    {
        $tracer = new Tracer();
        $scope = $tracer->startActiveSpan('name');
        $activeSpan = $tracer->getActiveSpan();

        $this->assertEquals($scope->getSpan(), $activeSpan);
    }

    /** @test */
    public function it_can_start_a_child_span(): void
    {
        $tracer = new Tracer;
        $parentScope = $tracer->startActiveSpan('parent');
        $parentScope->getSpan()->addBaggageItem('baggage', 'item');
        $childScope = $tracer->startActiveSpan('child');

        $this->assertEquals(
            $parentScope->getSpan()->getContext()->getSpanId(),
            $childScope->getSpan()->getContext()->getParentId()
        );

        $this->assertEquals('item', $childScope->getSpan()->getContext()->getBaggageItem('baggage'));
    }

    /**
     * @test
     */
    public function it_can_start_a_span(): void
    {
        $tracer = new Tracer();
        $tracer->startSpan('name');
        $activeSpan = $tracer->getActiveSpan();

        $this->assertNull($activeSpan);
    }

    /**
     * @test
     */
    public function it_cannot_inject_with_no_injectors(): void
    {
        $tracer = new Tracer();
        $span = $tracer->startSpan('name');
        $carrier = [];

        $this->expectException(UnsupportedFormat::class);
        $tracer->inject($span->getContext(), 'format', $carrier);
    }

    /**
     * @test
     */
    public function it_can_inject(): void
    {
        $actualSpanContext = null;
        $actualCarrier = null;

        $injector = function ($spanContext, $carrier) use (&$actualSpanContext, &$actualCarrier) {
            $actualSpanContext = $spanContext;
            $actualCarrier = $carrier;
        };

        $tracer = new Tracer(['format' => $injector]);
        $span = $tracer->startSpan('name');
        $carrier = [];
        $tracer->inject($span->getContext(), 'format', $carrier);

        $this->assertSame($span->getContext(), $actualSpanContext);
        $this->assertSame($carrier, $actualCarrier);
    }

    /**
     * @test
     */
    public function it_cannot_extract_with_no_extractors(): void
    {
        $tracer = new Tracer();
        $carrier = [];

        $this->expectException(UnsupportedFormat::class);
        $tracer->extract('format', $carrier);
    }

    /**
     * @test
     */
    public function it_can_extract(): void
    {
        $actualSpanContext = null;
        $actualCarrier = null;

        $extractor = function ($carrier) use (&$actualCarrier) {
            $actualCarrier = $carrier;
            return NoopSpan::create();
        };

        $tracer = new Tracer([], ['format' => $extractor]);
        $carrier = [
            'TRACE_ID' => 'trace_id'
        ];

        $spanContext = $tracer->extract('format', $carrier);

        $this->assertInstanceOf(OTSpan::class, $spanContext);
    }

    /**
     * @test
     */
    public function it_can_flush(): void
    {
        $tracer = new Tracer();
        $tracer->startSpan('name');

        $this->assertCount(1, $tracer->getSpans());

        $tracer->flush();

        $this->assertCount(0, $tracer->getSpans());
    }

    /** @test */
    public function it_can_flush_to_a_transport(): void
    {
        $tracer = new Tracer();

        $transport = Mockery::mock(TransportInterface::class);
        $transport->shouldReceive('send');

        $tracer->registerTransport($transport);
        $this->assertCount(1, $tracer->getTransports());

        $tracer->flush();

        $transport->shouldHaveReceived('send');
    }
}
