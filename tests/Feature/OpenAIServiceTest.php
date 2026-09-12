<?php

use App\Services\OpenAIService;

// test('open ai service test', function () {
//     $service = new OpenAIService();
//     $response = $service->client()->chat()->create([
//         'model' => 'gpt-3.5-turbo',
//         'messages' => [
//             [
//                 'role' => 'user',
//                 'content' => 'Hello, how are you?',
//             ],
//         ],
//     ]);
//     expect($response)->not->toBeNull();
// });

test('get rfps plateforms test', function () {
    $service = new OpenAIService();

    $response = $service->client()->responses()->create([
        'model' => 'gpt-5.6-luna',

        'tools' => [
            [
                'type' => 'web_search',
            ],
        ],

        'input' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => <<<'PROMPT'
                                    Find procurement platforms used by educational institutions in Texas, USA.

                                Search the web extensively.

                                Focus on:
                                    - Universities
                                    - Colleges
                                    - Community colleges
                                    - Schools
                                    - School districts

                                Find:
                                    1. Texas government/education procurement portals
                                    2. Third-party procurement platforms used by Texas institutions
                                    3. Institution-specific procurement portals

                                IMPORTANT:
                                    - Do NOT return individual RFPs.
                                    - Find the platforms/websites where RFPs, bids, tenders,
                                    solicitations, or procurement opportunities are published.
                                    - Search multiple Texas educational institutions.
                                    - Verify every platform using an institutional or government
                                    evidence URL.
                                    - Deduplicate platforms.
                                    - Do not stop after finding only a few platforms.
                                    - Continue searching until additional searches produce
                                    no materially new platforms.

                                For each platform return:
                                    name
                                    domain
                                    url
                                    platform_type
                                    country
                                    state
                                    city
                                    institution_type
                                    coverage
                                    is_public
                                    requires_login
                                    evidence_url
                                    institutions_using_it
                                    confidence

                                Return JSON only.
                        PROMPT
                    ],
                ],
            ],
        ],
    ]);

    expect($response->outputText)->not->toBeNull();
})->skip('Requires live OpenAI API connection');
