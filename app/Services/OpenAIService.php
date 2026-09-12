<?php

namespace App\Services;

use OpenAI;
use OpenAI\Contracts\ClientContract;

class OpenAIService
{
    protected ClientContract $client;

    public function __construct()
    {
        $this->client = OpenAI::client(
            config('services.openai.api_key')
        );
    }

    public function client(): ClientContract
    {
        return $this->client;
    }
}
