<?php

namespace OpenTracingClient;

interface TransportInterface
{
    public function send(array $spans): void;
}