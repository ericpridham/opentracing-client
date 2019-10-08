<?php

namespace OpenTracingClient;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use OpenTracing\Span as OTSpan;
use OpenTracing\SpanContext as OTSpanContext;

class Span implements OTSpan, \JsonSerializable
{
    /**
     * @var string
     */
    private $operationName;

    /**
     * @var OTSpanContext
     */
    private $context;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var array
     */
    private $logs = [];

    /**
     * @var CarbonInterface
     */
    private $startTime;

    /**
     * @var CarbonInterface|null
     */
    private $finishTime;

    public function __construct($operationName, OTSpanContext $context, CarbonInterface $startTime = null)
    {
        $this->operationName = $operationName;
        $this->context = $context;
        $this->startTime = $startTime ?: CarbonImmutable::now();
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationName()
    {
        return $this->operationName;
    }

    /**
     * {@inheritdoc}
     * @return OTSpanContext|OTSpanContext
     */
    public function getContext()
    {
        return $this->context;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getFinishTime()
    {
        return $this->finishTime;
    }

    /**
     * {@inheritdoc}
     */
    public function finish($finishTime = null): void
    {
        $this->finishTime = $finishTime ?: CarbonImmutable::now();
    }

    public function isFinished(): bool
    {
        return $this->finishTime !== null;
    }

    public function getDurationMs(): int
    {
        if (! $this->isFinished()) {
            return 0;
        }
        return $this->finishTime->diffInRealMilliseconds($this->startTime);
    }

    /**
     * {@inheritdoc}
     */
    public function overwriteOperationName($newOperationName): void
    {
        $this->operationName = (string)$newOperationName;
    }

    /**
     * {@inheritdoc}
     */
    public function setTag($key, $value): void
    {
        $this->tags[$key] = $value;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function log(array $fields = [], $timestamp = null): void
    {
        $this->logs[] = [
            'timestamp' => $timestamp ?: time(),
            'fields' => $fields,
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * {@inheritdoc}
     */
    public function addBaggageItem($key, $value): void
    {
        $this->context = $this->context->withBaggageItem($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaggageItem($key): ?string
    {
        return $this->context->getBaggageItem($key);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return [
            'operationName' => $this->operationName,
            'context' => $this->context,
            'tags' => $this->tags,
            'logs' => $this->logs,
            'starTime' => $this->startTime,
            'finishTime' => $this->finishTime,
            'durationMs' => $this->getDurationMs(),
        ];
    }
}
