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

    public function tools(): array
    {
        return [
            [
                'type' => 'web_search',
            ],
        ];
    }

    public function content(array $messages, ?string $input = null, ?array $tools = []): string
    {
        Log::info('OpenAI content initialized');
        Log::info($messages);
        if (! empty($tools)) {
            try {
                $response = $this->client->responses()->create([
                    'model' => 'gpt-5.6-luna',

                    'tools' => [
                        [
                            'type' => 'web_search',
                        ],
                    ],

                    'input' => $input,
                ]);
                Log::info('OpenAI tools response', ['response' => $response]);

                return trim($response->outputText);
            } catch (\Throwable $e) {
                Log::info('OpenAI tools error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

                return '';
            }
        } else {
            try {
                $response = $this->client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => $messages,
                    'temperature' => 0,
                ]);

                Log::info('OpenAI prompt response', ['response' => $response]);

                return trim($response->choices[0]->message->content ?? '');
            } catch (\Throwable $e) {
                Log::info('OpenAI prompt error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

                return '';
            }
        }
    }
}
