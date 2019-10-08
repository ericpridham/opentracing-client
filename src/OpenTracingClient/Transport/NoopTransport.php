<?php


namespace App\Services\Opentracing\Transport;


use App\Services\Opentracing\TransportInterface;

class NoopTransport implements TransportInterface
{
    /**
     * @param array $spans
     */
    public function send(array $spans): void
    {
    }
}