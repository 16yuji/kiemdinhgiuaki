<?php

return [
    'provider' => env('TRAVELMATE_AI_PROVIDER', 'fallback'),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    'openai_endpoint' => env('OPENAI_ENDPOINT', 'https://api.openai.com/v1/responses'),
    'timeout' => (int) env('TRAVELMATE_AI_TIMEOUT', 18),
    'review_min_count' => (int) env('AI_REVIEW_MIN_COUNT', 1),
    'review_refresh_step' => (int) env('AI_REVIEW_REFRESH_STEP', 10),
];
