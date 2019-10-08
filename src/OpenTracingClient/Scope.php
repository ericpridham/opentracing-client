<?php

namespace OpenTracingClient;

use OpenTracing\Scope as OTScope;
use OpenTracing\Span as OTSpan;

final class Scope implements OTScope
{
    /**
     * @var OTSpan
     */
    private $span;

    /**
     * @var ScopeManager
     */
    private $scopeManager;

    /**
     * @var bool
     */
    private $finishSpanOnClose;

    /**
     * @param ScopeManager $scopeManager
     * @param OTSpan $span
     * @param bool $finishSpanOnClose
     */
    public function __construct(
        ScopeManager $scopeManager,
        OTSpan $span,
        $finishSpanOnClose
    ) {
        $this->scopeManager = $scopeManager;
        $this->span = $span;
        $this->finishSpanOnClose = $finishSpanOnClose;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->finishSpanOnClose) {
            $this->span->finish();
        }

        $this->scopeManager->deactivate($this);
    }

    /**
     * {@inheritdoc}
     * @return OTSpan|OTSpan
     */
    public function getSpan()
    {
        return $this->span;
    }
}
