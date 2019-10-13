<?php

namespace OpenTracingClient\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class HoneycombClient
{
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $dataset;
    /**
     * @var Client|ClientInterface
     */
    private $client;

    public function __construct(string $apiKey, string $dataset, ClientInterface $client = null)
    {
        $this->apiKey = $apiKey;
        $this->dataset = $dataset;
        $this->client = $client ?? new Client();
    }

    public function send(string $payload)
    {
        $res = $this->client->request('POST', 'https://api.honeycomb.io/1/batch/' . $this->dataset, [
            'headers' => [
                'X-Honeycomb-Team' => $this->apiKey,
            ],
            'body' => $payload
        ]);
    }
}