<?php

return [
    'llm' => [
        'api_key'        => env('LLM_API_KEY'),
        'model'          => env('LLM_MODEL', 'llama-3.3-70b-versatile'),
        'base_url'       => env('BASE_URL'),
        'max_iterations' => env('MAX_ITERATIONS', 8)
    ],
];
