<?php

namespace OpenTracingClient;

use OpenTracing\Scope as OTScope;
use OpenTracing\ScopeManager as OTScopeManager;
use OpenTracing\Span as OTSpan;

final class ScopeManager implements OTScopeManager
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
        $scope = new Scope($this, $span, $finishSpanOnClose);
        $this->scopes[] = $scope;
        return $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getActive()
    {
        if (empty($this->scopes)) {
            return null;
        }

        return $this->scopes[count($this->scopes) - 1];
    }

    public function deactivate(Scope $scope)
    {
        foreach ($this->scopes as $scopeIndex => $scopeItem) {
            if ($scope === $scopeItem) {
                unset($this->scopes[$scopeIndex]);
            }
        }
    }
}
