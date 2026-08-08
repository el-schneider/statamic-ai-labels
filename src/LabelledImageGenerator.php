<?php

namespace ElSchneider\StatamicAiLabels;

use Statamic\Imaging\ImageGenerator;

class LabelledImageGenerator extends ImageGenerator
{
    public function generateByAsset($asset, array $params)
    {
        return parent::generateByAsset($asset, array_merge($params, self::watermarkParams($asset, $params)));
    }

    public static function watermarkParams($asset, array $params): array
    {
        // Remote URLs and bare paths have no asset to classify — never badge them.
        if ($asset === null) {
            return [];
        }

        // L3 owns this marker; its burned original must not receive a second badge.
        if (array_key_exists('mark', $params) || $asset->get('ai_label_badged')) {
            return [];
        }

        if (! $label = ServiceProvider::label($asset->get(config('ai_labels.field_handle')))) {
            return [];
        }

        $badge = config("ai_labels.badges.{$label}");

        if (! file_exists(public_path($badge))) {
            app()->getProvider(ServiceProvider::class)->warnMissingBadgesOnce();

            return [];
        }

        $width = self::derivativeWidth($asset, $params);
        $small = $width < config('ai_labels.small_derivative.threshold');

        if ($small && config('ai_labels.small_derivative.behavior') === 'skip') {
            return [];
        }

        $markWidth = $small ? 24 : self::badgeWidth($width);

        return [
            'mark' => $badge,
            'markw' => $markWidth,
            'markpad' => max(4, (int) round($markWidth / 4)),
            'markpos' => config('ai_labels.position', 'bottom-right'),
        ];
    }

    public static function badgeWidth(int $width): int
    {
        return max(
            config('ai_labels.clamp.min'),
            min(config('ai_labels.clamp.max'), (int) round(0.08 * $width))
        );
    }

    private static function derivativeWidth($asset, array $params): int
    {
        if (isset($params['w'])) {
            return (int) $params['w'];
        }

        // Glide preserves aspect ratio for height-only requests, so derive its width from the source.
        if (isset($params['h'])) {
            return (int) round($params['h'] * $asset->width() / $asset->height());
        }

        return $asset->width();
    }
}
