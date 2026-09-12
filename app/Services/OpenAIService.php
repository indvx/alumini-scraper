<?php

namespace App\Services;

use OpenAI;
use OpenAI\Client;

class OpenAIService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = OpenAI::client(
            config('services.openai.api_key')
        );
    }

    public function client(): Client
    {
        return $this->client;
    }
}
