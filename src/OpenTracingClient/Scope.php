<?php

namespace OpenTracingClient;

use OpenTracing\Scope as OTScope;
use OpenTracing\Span as OTSpan;

class Scope implements OTScope
{
    /**
     * @var OTSpan
     */
    private $span;

    /**
     * @var bool
     */
    private $finishSpanOnClose;

    /** @var bool */
    private $closed;

    /**
     * @param ScopeManager $scopeManager
     * @param OTSpan $span
     * @param bool $finishSpanOnClose
     */
    public function __construct(OTSpan $span, $finishSpanOnClose)
    {
        $this->span = $span;
        $this->finishSpanOnClose = $finishSpanOnClose;
        $this->closed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->finishSpanOnClose) {
            $this->span->finish();
        }

        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     * @return OTSpan|OTSpan
     */
    public function getSpan()
    {
        return $this->span;
    }

    public function isClosed(): bool
    {
        returN $this->closed;
    }
}
