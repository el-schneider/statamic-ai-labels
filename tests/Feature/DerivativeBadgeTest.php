<?php

use ElSchneider\StatamicAiLabels\LabelledImageGenerator;
use Illuminate\Support\Facades\Log;
use Statamic\Assets\Asset;
use Statamic\Http\Controllers\CP\Assets\ThumbnailController;
use Statamic\Imaging\ImageGenerator;

function generatedImage(Asset $asset, array $params = []): GdImage
{
    $path = app(ImageGenerator::class)->generateByAsset($asset, $params);

    return imagecreatefrompng(storage_path('statamic/glide/'.$path));
}

test('a glide derivative of a flagged asset contains the badge', function () {
    $image = generatedImage(labelledAsset(), ['w' => 400]);

    expect(imagecolorat($image, 390, 290))->not->toBe(imagecolorallocate($image, 220, 20, 60));
});

test('a glide derivative of an unflagged asset is untouched', function () {
    $image = generatedImage(labelledAsset('unexpected'), ['w' => 400]);
    $color = imagecolorsforindex($image, imagecolorat($image, 390, 290));

    expect($color)->toMatchArray(['red' => 220, 'green' => 20, 'blue' => 60]);
});

test('an explicit mark param in the request wins over the injected badge', function () {
    $image = generatedImage(labelledAsset(), ['w' => 400, 'mark' => 'vendor/statamic-ai-labels/badges/ai.png']);

    expect(imagecolorsforindex($image, imagecolorat($image, 390, 290))['red'])->toBeLessThan(100);
});

test('mark set to empty string suppresses the badge', function () {
    $image = generatedImage(labelledAsset(), ['w' => 400, 'mark' => '']);
    $color = imagecolorsforindex($image, imagecolorat($image, 390, 290));

    expect($color)->toMatchArray(['red' => 220, 'green' => 20, 'blue' => 60]);
});

test('an original already carrying a burned badge is not badged again', function () {
    $image = generatedImage(labelledAsset()->set('ai_label_badged', true), ['w' => 400]);
    $color = imagecolorsforindex($image, imagecolorat($image, 390, 290));

    expect($color)->toMatchArray(['red' => 220, 'green' => 20, 'blue' => 60]);
});

test('a missing badge warns before glide delegates without a watermark', function () {
    config()->set('ai_labels.badges.generated', 'missing-badge.png');
    Log::spy();

    $image = generatedImage(labelledAsset(), ['w' => 400]);
    $color = imagecolorsforindex($image, imagecolorat($image, 390, 290));

    expect($color)->toMatchArray(['red' => 220, 'green' => 20, 'blue' => 60]);
    Log::shouldHaveReceived('warning')->once()->with('AI Labels badge image is missing: missing-badge.png');
});

test('badge size respects the clamp at small, medium and large derivative widths', function () {
    expect(LabelledImageGenerator::badgeWidth(100))->toBe(32)
        ->and(LabelledImageGenerator::badgeWidth(800))->toBe(64)
        ->and(LabelledImageGenerator::badgeWidth(2000))->toBe(96);
});

test('small derivatives get the minimal icon badge instead of nothing', function () {
    $image = generatedImage(labelledAsset(), ['w' => 100]);

    expect(imagecolorat($image, 90, 65))->not->toBe(imagecolorallocate($image, 220, 20, 60));
});

test('cp thumbnails of flagged assets show the badge', function () {
    $controller = app(ThumbnailController::class);
    $asset = labelledAsset();
    $property = new ReflectionProperty($controller, 'asset');
    $property->setValue($controller, $asset);
    $method = new ReflectionMethod($controller, 'generate');
    $image = imagecreatefrompng(storage_path('statamic/glide/'.$method->invoke($controller)));

    expect(imagecolorat($image, 390, 290))->not->toBe(imagecolorallocate($image, 220, 20, 60));
});

test('a configured custom badge image is used instead of the EU pill', function () {
    $custom = imagecreatetruecolor(40, 40);
    imagefill($custom, 0, 0, imagecolorallocate($custom, 0, 255, 0));
    imagepng($custom, public_path('custom-badge.png'));
    imagedestroy($custom);
    config()->set('ai_labels.badges.generated', 'custom-badge.png');

    $image = generatedImage(labelledAsset(), ['w' => 400]);
    $color = imagecolorsforindex($image, imagecolorat($image, 390, 290));

    expect($color)->toMatchArray(['red' => 0, 'green' => 255, 'blue' => 0]);
});

test('generator subclass delegates untouched for path- and url-based generation', function () {
    $generator = app(ImageGenerator::class);

    expect($generator)->toBeInstanceOf(LabelledImageGenerator::class)
        ->and((new ReflectionMethod($generator, 'generateByPath'))->getDeclaringClass()->getName())->toBe(ImageGenerator::class)
        ->and((new ReflectionMethod($generator, 'generateByUrl'))->getDeclaringClass()->getName())->toBe(ImageGenerator::class);
});

test('a squarish badge is scaled down by its configured scale', function () {
    config()->set('ai_labels.badges.generated', ['path' => config('ai_labels.badges.generated'), 'scale' => 0.5]);

    $params = LabelledImageGenerator::watermarkParams(labelledAsset(), ['w' => 1000]);

    // Unscaled clamp for w=1000 is 80; scale 0.5 halves it.
    expect($params['markw'])->toBe(40);
});

test('derivatives below the configured min width carry no badge', function () {
    config()->set('ai_labels.small_derivative.min_width', 150);

    $params = LabelledImageGenerator::watermarkParams(labelledAsset(), ['w' => 100]);

    expect($params)->toBe([]);
});

test('padding is a fraction of the badge width', function () {
    $params = LabelledImageGenerator::watermarkParams(labelledAsset(), ['w' => 1000]);

    // Clamp for w=1000 is 80; default padding 0.25 gives 20.
    expect($params['markpad'])->toBe(20);
});

test('zero padding places the badge flush with the edge', function () {
    config()->set('ai_labels.padding', 0);

    $image = generatedImage(labelledAsset(), ['w' => 400]);

    expect(LabelledImageGenerator::watermarkParams(labelledAsset(), ['w' => 400])['markpad'])->toBe(0)
        ->and(imagecolorat($image, 384, 297))->not->toBe(imagecolorallocate($image, 220, 20, 60));
});
