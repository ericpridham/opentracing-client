<?php

namespace OpenTracingClient;

use OpenTracing\Exceptions\UnsupportedFormat;
use OpenTracing\ScopeManager as OTScopeManager;
use OpenTracing\StartSpanOptions as OTStartSpanOptions;
use OpenTracing\Tracer as OTTracer;
use OpenTracing\SpanContext as OTSpanContext;

class Tracer implements OTTracer
{
    /**
     * @var array|Span[]
     */
    private $spans = [];

    /**
     * @var array|callable[]
     */
    private $injectors;

    /**
     * @var array|callable[]
     */
    private $extractors;

    /**
     * @var OTScopeManager
     */
    private $scopeManager;
    /**
     * @var array|TransportInterface[]
     */
    private $transports;

    public function __construct(array $injectors = [], array $extractors = [])
    {
        $this->injectors = $injectors;
        $this->extractors = $extractors;
        $this->scopeManager = new ScopeManager();
        $this->transports = [];
    }

    /**
     * {@inheritdoc}
     */
    public function startActiveSpan($operationName, $options = null)
    {
        if (!($options instanceof OTStartSpanOptions)) {
            $options = OTStartSpanOptions::create($options??[]);
        }

        if (($activeSpan = $this->getActiveSpan()) !== null) {
            $options = $options->withParent($activeSpan);
        }

        $span = $this->startSpan($operationName, $options);

        return $this->scopeManager->activate($span, $options->shouldFinishSpanOnClose());
    }

    /**
     * {@inheritdoc}
     */
    public function startSpan($operationName, $options = null)
    {
        if (!($options instanceof OTStartSpanOptions)) {
            $options = OTStartSpanOptions::create($options??[]);
        }

        if (empty($options->getReferences())) {
            $spanContext = SpanContext::createAsRoot();
        } else {
            $spanContext = SpanContext::createAsChildOf($options->getReferences()[0]->getContext());
        }

        $span = new Span(
            $operationName,
            $spanContext,
            $options->getStartTime()
        );

        foreach ($options->getTags() as $key => $value) {
            $span->setTag($key, $value);
        }

        $this->spans[] = $span;

        return $span;
    }

    /**
     * {@inheritdoc}
     */
    public function inject(OTSpanContext $spanContext, $format, &$carrier)
    {
        if (!array_key_exists($format, $this->injectors)) {
            throw UnsupportedFormat::forFormat($format);
        }

        call_user_func($this->injectors[$format], $spanContext, $carrier);
    }

    /**
     * {@inheritdoc}
     */
    public function extract($format, $carrier)
    {
        if (!array_key_exists($format, $this->extractors)) {
            throw UnsupportedFormat::forFormat($format);
        }

        return call_user_func($this->extractors[$format], $carrier);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        foreach ($this->transports as $transport) {
            $transport->send($this->spans);
        }
        $this->spans = [];
    }

    /**
     * @return array|Span[]
     */
    public function getSpans()
    {
        return $this->spans;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeManager()
    {
        return $this->scopeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveSpan()
    {
        if (null !== ($activeScope = $this->scopeManager->getActive())) {
            return $activeScope->getSpan();
        }

        return null;
    }

    public function registerTransport(TransportInterface $param)
    {
        $this->transports[] = $param;
    }

    public function getTransports()
    {
        return $this->transports;
    }
}
