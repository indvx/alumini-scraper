<?php

use App\Services\AiRFPsPlatformGeneratorService;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Contracts\ClientContract;
use OpenAI\Contracts\Resources\ResponsesContract;
use OpenAI\Responses\Responses\CreateResponse;
use OpenAI\Testing\Enums\OverrideStrategy;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('generateFromPrompt parses outputText from OpenAI CreateResponse correctly', function () {
    $mockJson = json_encode([
        [
            'name' => 'Texas SmartBuy Portal',
            'domain' => 'txsmartbuy.com',
            'url' => 'https://www.txsmartbuy.com/esbd',
            'platform_type' => 'State Portal',
            'country' => 'United States',
            'state' => 'Texas',
            'city' => 'Austin',
            'institution_type' => 'Government & Higher Ed',
            'coverage' => 'Statewide',
            'is_public' => true,
            'requires_login' => false,
        ],
    ]);

    $createResponse = CreateResponse::fake([
        'output' => [
            [
                'type' => 'message',
                'id' => 'msg_123',
                'status' => 'completed',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'output_text',
                        'text' => $mockJson,
                        'annotations' => [],
                    ],
                ],
            ],
        ],
    ], strategy: OverrideStrategy::Replace);

    $responsesMock = Mockery::mock(ResponsesContract::class);
    $responsesMock->shouldReceive('create')
        ->once()
        ->andReturn($createResponse);

    $clientMock = Mockery::mock(ClientContract::class);
    $clientMock->shouldReceive('responses')
        ->once()
        ->andReturn($responsesMock);

    $openAiServiceMock = Mockery::mock(OpenAIService::class);
    $openAiServiceMock->shouldReceive('client')
        ->once()
        ->andReturn($clientMock);

    $service = new AiRFPsPlatformGeneratorService($openAiServiceMock);
    $result = $service->generateFromPrompt('United States', 'Texas');

    expect($result['createdCount'])->toBe(1);
    expect($result['createdPlatforms'])->toHaveCount(1);

    $this->assertDatabaseHas('rfps_platforms', [
        'name' => 'Texas SmartBuy Portal',
        'domain' => 'txsmartbuy.com',
        'country' => 'United States',
        'state' => 'Texas',
    ]);
});

test('generateFromPrompt handles markdown code fences in outputText', function () {
    $mockJson = "```json\n".json_encode([
        [
            'name' => 'AggieBid Portal',
            'domain' => 'tamus.edu',
            'url' => 'https://www.tamus.edu',
            'platform_type' => 'University Portal',
            'country' => 'United States',
            'state' => 'Texas',
            'city' => 'College Station',
        ],
    ])."\n```";

    $createResponse = CreateResponse::fake([
        'output' => [
            [
                'type' => 'message',
                'id' => 'msg_456',
                'status' => 'completed',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'output_text',
                        'text' => $mockJson,
                        'annotations' => [],
                    ],
                ],
            ],
        ],
    ], strategy: OverrideStrategy::Replace);

    $responsesMock = Mockery::mock(ResponsesContract::class);
    $responsesMock->shouldReceive('create')
        ->once()
        ->andReturn($createResponse);

    $clientMock = Mockery::mock(ClientContract::class);
    $clientMock->shouldReceive('responses')
        ->once()
        ->andReturn($responsesMock);

    $openAiServiceMock = Mockery::mock(OpenAIService::class);
    $openAiServiceMock->shouldReceive('client')
        ->once()
        ->andReturn($clientMock);

    $service = new AiRFPsPlatformGeneratorService($openAiServiceMock);
    $result = $service->generateFromPrompt('United States', 'Texas');

    expect($result['createdCount'])->toBe(1);
    $this->assertDatabaseHas('rfps_platforms', [
        'name' => 'AggieBid Portal',
        'domain' => 'tamus.edu',
    ]);
});
