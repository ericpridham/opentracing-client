<?php

namespace OpenTracingClient;

use ArrayIterator;
use JsonSerializable;
use OpenTracing\SpanContext as OTSpanContext;
use Ramsey\Uuid\Uuid;

final class SpanContext implements OTSpanContext, JsonSerializable
{
    /**
     * @var string
     */
    private $traceId;

    /**
     * @var string
     */
    private $spanId;

    /**
     * @var string
     */
    private $parentId;

    /**
     * @var bool
     */
    private $isSampled;

    /**
     * @var array
     */
    private $items;

    private function __construct(string $traceId, string $spanId, ?string $parentId, bool $isSampled, array $items)
    {
        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->parentId = $parentId;
        $this->isSampled = $isSampled;
        $this->items = $items;
    }

    public static function create(string $traceId, string $spanId, string $parentId = null, bool $sampled = true, array $items = [])
    {
        return new self($traceId, $spanId, $parentId, $sampled, $items);
    }

    public static function createAsRoot(bool $sampled = true, array $items = [])
    {
        $traceId = $spanId = self::nextId();
        return new self($traceId, $spanId, null, $sampled, $items);
    }

    public static function createAsChildOf(OTSpanContext $spanContext)
    {
        $spanId = self::nextId();
        return new self($spanContext->traceId, $spanId, $spanContext->getSpanId(), $spanContext->isSampled, $spanContext->items);
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getSpanId(): string
    {
        return $this->spanId;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function isSampled()
    {
        return $this->isSampled;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaggageItem($key)
    {
        return array_key_exists($key, $this->items) ? $this->items[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function withBaggageItem($key, $value)
    {
        return new self($this->traceId, $this->spanId, $this->parentId, $this->isSampled, array_merge($this->items, [$key => $value]));
    }

    private static function nextId()
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'traceId' => $this->traceId,
            'spanId' => $this->spanId,
            'parentId' => $this->parentId,
            'isSampled' => $this->isSampled,
        ];
    }
}
