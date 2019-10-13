<?php

namespace OpenTracingClient;

use OpenTracing\Scope as OTScope;
use OpenTracing\ScopeManager as OTScopeManager;
use OpenTracing\Span as OTSpan;

class ScopeManager implements OTScopeManager
{
    /**
     * @var array|OTScope[]
     */
    private $scopes = [];

    /**
     * {@inheritdoc}
     */
    public function activate(OTSpan $span, $finishSpanOnClose = OTScopeManager::DEFAULT_FINISH_SPAN_ON_CLOSE)
    {
        $scope = new Scope($span, $finishSpanOnClose);
        $this->scopes[] = $scope;
        return $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getActive()
    {
        $this->popClosedScopes();

        if (empty($this->scopes)) {
            return null;
        }

        return $this->scopes[array_key_last($this->scopes)];
    }

    private function popClosedScopes(): void
    {
        while (array_key_last($this->scopes) !== null && $this->scopes[array_key_last($this->scopes)]->isClosed()) {
            array_pop($this->scopes);
        }
    }
}
