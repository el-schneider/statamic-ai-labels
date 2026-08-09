<?php

use ElSchneider\StatamicAiLabels\BadgedImageNode;
use ElSchneider\StatamicAiLabels\Tags\AiLabel;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Providers\BardServiceProvider;

function renderAntlers(string $template, array $data): string
{
    AiLabel::register();

    return (string) Antlers::parse($template, $data, true);
}

function renderBardImage(string $src): string
{
    Augmentor::replaceExtension('image', new BadgedImageNode);
    (new BardServiceProvider(app()))->boot();
    $bard = (new Bard)->setField(new Field('content', ['type' => 'bard']));

    return $bard->augment($bard->preProcess('<img src="'.$src.'">'));
}

test('a bard image referencing a flagged asset renders with the badge markup', function () {
    $asset = labelledAsset();

    expect(renderBardImage('asset::'.$asset->id()))
        ->toContain('<figure class="ai-label-bard"')
        ->toContain('data-ai-label="generated"')
        ->toContain('AI-generated content');
});

test('a bard image referencing an unflagged asset renders unchanged', function () {
    $asset = labelledAsset('unexpected');

    // Bard renders raw asset URLs, not Glide URLs, so L1 cannot add a second badge here.
    expect(renderBardImage('asset::'.$asset->id()))
        ->toBe('<img>');
});

test('bard rendering survives an image whose src resolves to no asset', function () {
    expect(renderBardImage('https://example.com/image.png'))
        ->toBe('<img src="https://example.com/image.png">');
});

test('the ai_label tag renders the badge wrapper for a flagged asset', function () {
    $asset = labelledAsset();

    expect(renderAntlers('{{ ai_label :asset="asset" }}<img src="image.png">{{ /ai_label }}', ['asset' => $asset]))
        ->toContain('class="ai-label-wrapper"')
        ->toContain('data-ai-label="generated"')
        ->toContain('<img src="image.png">');
});

test('the ai_label tag renders nothing for an unflagged asset', function () {
    $asset = labelledAsset('unexpected');

    expect(renderAntlers('{{ ai_label :asset="asset" }}<img src="image.png">{{ /ai_label }}', ['asset' => $asset]))
        ->toBe('<img src="image.png">');
});

test('images rendered through the ai_label tag carry no burned-in badge', function () {
    $asset = labelledAsset();

    expect(renderAntlers('{{ ai_label :asset="asset" }}{{ glide :src="asset" mark="" }}{{ /ai_label }}', ['asset' => $asset]))
        ->toContain('class="ai-label-wrapper"')
        ->toContain('mark=')
        ->not->toContain('mark=vendor%2Fstatamic-ai-labels%2Fbadges%2Fgenerated.png');
});

test('the badge is not clickable', function () {
    $asset = labelledAsset();

    expect(renderAntlers('{{ ai_label:badge :asset="asset" }}', ['asset' => $asset]))
        ->toContain('pointer-events:none');
});

test('the badge exposes an accessible text alternative', function () {
    $asset = labelledAsset();

    expect(renderAntlers('{{ ai_label:badge :asset="asset" }}', ['asset' => $asset]))
        ->toContain('AI-generated content')
        ->toContain('class="sr-only"');
});
