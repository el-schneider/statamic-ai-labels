<?php

namespace ElSchneider\StatamicAiLabels;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetSaved;
use Statamic\Facades\Glide;
use Statamic\Facades\StaticCache;
use Statamic\Imaging\ImageGenerator;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishables = [
        __DIR__.'/../resources/badges' => 'badges',
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai_labels.php', 'ai_labels');

        app()->extend(ImageGenerator::class, fn (ImageGenerator $generator) => new LabelledImageGenerator($generator->getServer()));
        app()->extend(ImageManipulator::class, fn (ImageManipulator $manipulator) => new LabelledImageManipulator($manipulator));
    }

    public function boot(): void
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/ai_labels.php' => config_path('ai_labels.php'),
        ], 'ai-labels-config');

        Event::listen(AssetSaved::class, function (AssetSaved $event) {
            $handle = config('ai_labels.field_handle');

            if ($event->asset->getOriginal("data.{$handle}") !== $event->asset->get($handle)) {
                Glide::clearAsset($event->asset);
                StaticCache::flush();
            }
        });

        Event::listen(AssetContainerBlueprintFound::class, function (AssetContainerBlueprintFound $event) {
            if (! request()->is(trim(config('statamic.cp.route', 'cp'), '/').'/assets*')) {
                return;
            }

            $handle = config('ai_labels.field_handle');

            if ($event->blueprint->hasField($handle)) {
                return;
            }

            $contents = $event->blueprint->contents();
            $tab = array_key_first($contents['tabs']);
            $contents['tabs'][$tab]['sections'][0]['fields'][] = [
                'handle' => $handle,
                'field' => [
                    'type' => 'button_group',
                    'display' => __('ai_labels::labels.field'),
                    'options' => [
                        'ai' => __('ai_labels::labels.ai'),
                        'generated' => __('ai_labels::labels.generated'),
                        'modified' => __('ai_labels::labels.modified'),
                    ],
                ],
            ];
            $event->blueprint->setContents($contents);
        });

        $this->checkBadgeHealth();
    }

    public static function label(mixed $value): ?string
    {
        return in_array($value, ['ai', 'generated', 'modified'], true) ? $value : null;
    }

    public static function missingBadges(): array
    {
        return array_values(array_filter(config('ai_labels.badges'), fn (string $badge) => ! file_exists(public_path($badge))));
    }

    public function checkBadgeHealth(): void
    {
        foreach (self::missingBadges() as $badge) {
            Log::warning("AI Labels badge image is missing: {$badge}");
        }
    }

    public function warnMissingBadgesOnce(): void
    {
        if (request()->attributes->get('_ai_labels_badges_checked')) {
            return;
        }

        request()->attributes->set('_ai_labels_badges_checked', true);
        $this->checkBadgeHealth();
    }
}
