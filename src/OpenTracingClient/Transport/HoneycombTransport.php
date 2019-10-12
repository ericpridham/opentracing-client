<?php

namespace OpenTracingClient\Transport;

use OpenTracingClient\Span;
use OpenTracingClient\TransportInterface;

class HoneycombTransport implements TransportInterface
{
    /**
     * @var HoneycombClient
     */
    private $client;

    public function __construct(HoneycombClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param array|Span[] $spans
     */
    public function send(array $spans): void
    {
        $this->client->send(json_encode($this->translateSpans($spans)));
    }

    public function translateSpans($spans)
    {
        return array_map([self::class, 'translateSpan'], $spans);
    }

    public function translateSpan(Span $span): array
    {
        $data = [
            'name' => $span->getOperationName(),
            'trace.span_id' => $span->getContext()->getSpanId(),
            'trace.parent_id' => $span->getContext()->getParentId(),
            'trace.trace_id' => $span->getContext()->getTraceId(),
            'duration_ms' => $span->getDurationMs(),
            'startTime' => $span->getStartTime()->toJSON(),
            'finishTime' => $span->isFinished()?$span->getFinishTime()->toJSON(): null,
        ];
        // baggage
        foreach ($span->getContext() as $item => $value) {
            $data[$item] = $value;
        }
        foreach ($span->getTags() as $tag => $value) {
            $data[$tag] = $value;
        }

        return [
            'time' => $span->getStartTime()->toJSON(),
            'data' => $data
        ];
    }
}