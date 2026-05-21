<?php

return [
    'llm' => [
        'api_key'        => env('LLM_API_KEY'),
        'model'          => env('LLM_MODEL', 'llama-3.3-70b-versatile'),
        'base_url'       => env('BASE_URL'),
        'max_iterations' => (int) env('MAX_ITERATIONS', 8),
        'max_tokens'     => (int) env('MAX_TOKENS', 1024),
    ],
];
