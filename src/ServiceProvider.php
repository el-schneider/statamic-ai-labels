<?php

namespace ElSchneider\StatamicAiLabels;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetSaved;
use Statamic\Facades\Glide;
use Statamic\Facades\StaticCache;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Imaging\ImageGenerator;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [Tags\AiLabel::class];

    protected $publishables = [
        __DIR__.'/../resources/badges' => 'badges',
        __DIR__.'/../resources/views' => 'ai-labels-views',
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

        Augmentor::replaceExtension('image', new BadgedImageNode);

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
                    'display' => __('statamic-ai-labels::labels.field'),
                    'options' => [
                        'ai' => __('statamic-ai-labels::labels.ai'),
                        'generated' => __('statamic-ai-labels::labels.generated'),
                        'modified' => __('statamic-ai-labels::labels.modified'),
                    ],
                ],
            ];
            $event->blueprint->setContents($contents);
        });

        $this->publishShippedBadges();
        $this->checkBadgeHealth();
    }

    // A plain `composer require` never runs statamic:install, so the publishables
    // above stay unpublished and glide would silently render without badges — the
    // addon's worst failure mode. Self-heal: copy any missing shipped badge into
    // public on boot. Custom badge paths are untouched; checkBadgeHealth() still
    // warns loudly for those.
    public function publishShippedBadges(): void
    {
        $source = __DIR__.'/../resources/badges';
        $target = public_path('vendor/statamic-ai-labels/badges');

        foreach (File::allFiles($source) as $file) {
            $destination = $target.'/'.$file->getRelativePathname();

            // Copy when missing OR stale — shipped badge updates must reach
            // installed sites, and glide caches key on params, not file content.
            if (! file_exists($destination) || md5_file($destination) !== md5_file($file->getPathname())) {
                File::ensureDirectoryExists(dirname($destination));
                File::copy($file->getPathname(), $destination);
            }
        }
    }

    public static function label(mixed $value): ?string
    {
        return in_array($value, ['ai', 'generated', 'modified'], true) ? $value : null;
    }

    /** @return array{path: string, scale: float} */
    public static function badge(string $label): array
    {
        $entry = config("ai_labels.badges.{$label}");

        return is_array($entry)
            ? ['path' => $entry['path'], 'scale' => (float) ($entry['scale'] ?? 1.0)]
            : ['path' => $entry, 'scale' => 1.0];
    }

    public static function missingBadges(): array
    {
        return array_values(array_filter(
            array_map(fn (string $label) => self::badge($label)['path'], array_keys(config('ai_labels.badges'))),
            fn (string $path) => ! file_exists(public_path($path)),
        ));
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
