<?php

return [
    'badges' => [
        'ai' => 'vendor/statamic-ai-labels/badges/ai.png',
        'generated' => 'vendor/statamic-ai-labels/badges/generated.png',
        'modified' => 'vendor/statamic-ai-labels/badges/modified.png',
    ],
    'position' => 'bottom-right',
    'clamp' => ['min' => 32, 'max' => 96],
    'small_derivative' => ['threshold' => 200, 'behavior' => 'icon'],
    'field_handle' => 'ai_label',
];
