<?php

use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Events\AssetSaved;
use Statamic\Facades\Glide;
use Statamic\Facades\StaticCache;

function labelledGlideUrl($asset): string
{
    return app(ImageManipulator::class)->item($asset)->width(400)->build();
}

function imageBytes(GdImage $image): string
{
    ob_start();
    imagepng($image);

    return ob_get_clean();
}

test('toggling the label changes the glide URL for flagged assets', function () {
    config()->set('statamic.assets.image_manipulation.secure', true);
    $asset = labelledAsset('generated');

    $generated = labelledGlideUrl($asset);
    $asset->set('ai_label', 'modified');
    $modified = labelledGlideUrl($asset);

    expect($generated)->toContain('mark=vendor%2Fstatamic-ai-labels%2Fbadges%2Fgenerated.png')
        ->and($modified)->toContain('mark=vendor%2Fstatamic-ai-labels%2Fbadges%2Fmodified.png')
        ->and($generated)->toContain('s=')
        ->and($modified)->toContain('s=')
        ->and($modified)->not->toBe($generated);
});

test('toggling the label serves updated pixels to a client that cached the old derivative', function () {
    $asset = labelledAsset('generated');
    $cachedUrl = labelledGlideUrl($asset);
    $clientCache = [$cachedUrl => imageBytes(generatedImage($asset, ['w' => 400]))];

    $asset->set('ai_label', 'modified');
    $currentUrl = labelledGlideUrl($asset);
    $pixels = $clientCache[$currentUrl] ?? imageBytes(generatedImage($asset, ['w' => 400]));

    expect($currentUrl)->not->toBe($cachedUrl)
        ->and($clientCache)->not->toHaveKey($currentUrl)
        ->and($pixels)->not->toBe($clientCache[$cachedUrl]);
});

test('changing the label clears the asset glide derivatives', function () {
    $asset = labelledAsset('generated')->syncOriginal();
    $asset->set('ai_label', 'modified');
    Glide::shouldReceive('clearAsset')->once()->with($asset);
    StaticCache::shouldReceive('flush')->once();

    AssetSaved::dispatch($asset);
});

test('changing the label flushes the static page cache', function () {
    $asset = labelledAsset('generated')->syncOriginal();
    $asset->set('ai_label', 'modified');
    Glide::shouldReceive('clearAsset')->once()->with($asset);
    StaticCache::shouldReceive('flush')->once();

    AssetSaved::dispatch($asset);
});

test('saving an asset without touching the label does not flush the static cache', function () {
    $asset = labelledAsset('generated')->syncOriginal();
    Glide::shouldReceive('clearAsset')->never();
    StaticCache::shouldReceive('flush')->never();

    AssetSaved::dispatch($asset);
});
