<?php

return [
    // String path, or ['path' => ..., 'scale' => 0.4]. Scale multiplies the
    // computed badge width — use it for badges whose aspect ratio differs from
    // the wide text capsules (the square/round basic icon would otherwise cover
    // far more image area at the same width).
    'badges' => [
        'ai' => ['path' => 'vendor/statamic-ai-labels/badges/ai.png', 'scale' => 0.4],
        'generated' => 'vendor/statamic-ai-labels/badges/generated.png',
        'modified' => 'vendor/statamic-ai-labels/badges/modified.png',
    ],
    'position' => 'bottom-right',
    // Gap between badge and image edge, as a fraction of the badge width (0 = flush).
    'padding' => 0.25,
    'clamp' => ['min' => 32, 'max' => 96],
    'small_derivative' => [
        // Below threshold: render the round basic icon ('icon') or nothing ('skip').
        'threshold' => 200,
        'behavior' => 'icon',
        // Below min_width: never badge (0 = always badge). Derivatives this small
        // are documented as unlabelled — pair with the ai_label tag or adjacent disclosure.
        'min_width' => 0,
    ],
    'field_handle' => 'ai_label',
];
