<?php


namespace App\Services\Opentracing\Transport;


use App\Services\Opentracing\Span;
use App\Services\Opentracing\TransportInterface;
use Illuminate\Support\Facades\Log;

class LogfileTransport implements TransportInterface
{
    /**
     * @param array|Span[] $spans
     */
    public function send(array $spans): void
    {
        Log::debug(json_encode($spans));
    }
}