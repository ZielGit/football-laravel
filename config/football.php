<?php

return [
    'base_url' => env('FOOTBALL_DATA_BASE_URL', 'https://api.football-data.org/v4'),

    // El token SOLO se lee del .env. Nunca se escribe en el código ni en el repositorio.
    'token' => env('FOOTBALL_DATA_TOKEN'),

    'timeout' => (int) env('FOOTBALL_DATA_TIMEOUT', 10),

    // Plan gratuito de football-data.org: 10 peticiones por minuto.
    'rate_limit' => [
        'max_attempts' => (int) env('FOOTBALL_DATA_RATE_LIMIT', 10),
        'decay_seconds' => 60,
        'key' => 'football-data:api',
    ],

    // Tras cuántos minutos la lista de competiciones / una liga importada se considera vieja.
    'competitions_ttl_minutes' => (int) env('FOOTBALL_COMPETITIONS_TTL_MINUTES', 60),
    'import_ttl_minutes' => (int) env('FOOTBALL_IMPORT_TTL_MINUTES', 60),
];
