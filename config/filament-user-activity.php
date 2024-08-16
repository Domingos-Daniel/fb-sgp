<?php

// config for Edwink/FilamentUserActivity
return [
    'model' => \App\Models\User::class,
    'table' => [
        'name' => env('FILAMENT_USER_ACTIVITY_TABLE_NAME', 'user_activities'),
        'retention-days' => env('FILAMENT_USER_ACTIVITY_RETENTION_DAYS', 60),

        'active-users' => [
            'timeframe-selection' => [
                15 => '15 Minutos',
                30 => '30 Minutos',
                60 => '1 Hora',
                120 => '2 Horas',
                1440 => '24 Horas',
            ],
        ],
    ],
];
