<?php

use ElSchneider\StatamicAiLabels\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Statamic\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainerRepository;
use Statamic\Facades\AssetContainer;

uses(TestCase::class)->in('Feature');

function labelledAsset(string $label = 'generated', int $width = 400, int $height = 300): Asset
{
    File::ensureDirectoryExists(public_path('vendor/statamic-ai-labels/badges'));
    File::copyDirectory(__DIR__.'/../resources/badges', public_path('vendor/statamic-ai-labels/badges'));
    File::ensureDirectoryExists(public_path('ai-label-tests'));

    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 220, 20, 60));
    imagepng($image, public_path('ai-label-tests/source.png'));
    imagedestroy($image);

    config()->set('filesystems.disks.ai-label-tests', ['driver' => 'local', 'root' => public_path()]);

    $container = AssetContainer::make('assets')->disk('ai-label-tests');
    app(AssetContainerRepository::class)->save($container);

    $asset = $container->makeAsset('ai-label-tests/source.png')->hydrate()->set('ai_label', $label);
    $asset->saveQuietly();

    return $asset;
}
