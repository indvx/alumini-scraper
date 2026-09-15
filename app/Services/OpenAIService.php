<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
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
        Log::info('OpenAI client initialized');

        return $this->client;
    }

    public function prompt(string $promptText): string
    {
        Log::info('OpenAI prompt initialized');
        Log::info($promptText);
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            return '';
        }

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $promptText],
                ],
                'temperature' => 0.2,
            ]);

            Log::info('OpenAI prompt response', ['response' => $response]);

            return trim($response->choices[0]->message->content ?? '');
        } catch (\Throwable $e) {
            Log::info('OpenAI prompt error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return '';
        }
    }
}
