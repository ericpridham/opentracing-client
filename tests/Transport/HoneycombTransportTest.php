<?php

namespace Tests\Feature\Unit\Services\Opentracing\Transport;

use Carbon\CarbonImmutable;
use Mockery;
use OpenTracingClient\Span;
use OpenTracingClient\SpanContext;
use OpenTracingClient\Transport\HoneycombClient;
use OpenTracingClient\Transport\HoneycombTransport;
use PHPUnit\Framework\TestCase;

class HoneycombTransportTest extends TestCase
{
    /** @test */
    public function it_builds_the_right_span_structure(): void
    {
        $transport = new HoneycombTransport(Mockery::spy(HoneycombClient::class));

        $startTime = CarbonImmutable::now();
        $finishTime = $startTime->addMilliseconds(250);
        $context = SpanContext::create('trace-id', 'span-id', 'parent-id', true, [
            'item' => 'value',
        ]);

        $span = new Span('span', $context, $startTime);
        $span->setTag('tag', 'value');
        $span->finish($finishTime);

        $translated = $transport->translateSpan($span);
        $this->assertEquals([
            'time' => $startTime->toJSON(),
            'data' => [
                'name' => 'span',
                'trace.span_id' => 'span-id',
                'trace.parent_id' => 'parent-id',
                'trace.trace_id' => 'trace-id',
                'duration_ms' => 250,
                'startTime' => $startTime->toJSON(),
                'finishTime' => $finishTime->toJSON(),
                'item' => 'value',
                'tag' => 'value'
            ]
        ], $translated);
    }

    /** @test */
    public function it_sends_the_spans_to_honeycomb(): void
    {
        $client = Mockery::spy(HoneycombClient::class);
        $transport = new HoneycombTransport($client);

        $span = new Span('span', SpanContext::create('trace-id', 'span-id'), CarbonImmutable::now());

        $transport->send([$span, $span]);

        $client->shouldHaveReceived('send', function ($json) {
            $this->assertCount(2, json_decode($json, true));
            return true;
        });
    }
}