<?php

use ElSchneider\StatamicAiLabels\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\Assets\AssetsController;

function assetBlueprint(): Statamic\Fields\Blueprint
{
    return Blueprint::makeFromFields(['alt' => ['type' => 'text']]);
}

function useCpAssetRoute(): void
{
    $route = (new Route('GET', 'cp/assets/browse/images/photo.jpg/edit', fn () => null))->name('assets.browse.edit');

    app()->instance('request', Request::create('/cp/assets/browse/images/photo.jpg/edit'));
    request()->setRouteResolver(fn () => $route);
}

function useFrontendRoute(): void
{
    $route = (new Route('GET', '/', fn () => null))->name('home');

    app()->instance('request', Request::create('/'));
    request()->setRouteResolver(fn () => $route);
}

test('asset blueprint in the CP shows the ai_label field', function () {
    useCpAssetRoute();
    $blueprint = assetBlueprint();

    AssetContainerBlueprintFound::dispatch($blueprint);

    expect($blueprint->hasField('ai_label'))->toBeTrue()
        ->and($blueprint->fields()->get('ai_label')->config()['type'])->toBe('button_group')
        ->and(array_keys($blueprint->fields()->get('ai_label')->config()['options']))->toBe(['ai', 'generated', 'modified']);
});

test('frontend augmentation and GraphQL do not gain an ai_label field', function () {
    useFrontendRoute();
    $blueprint = assetBlueprint();

    AssetContainerBlueprintFound::dispatch($blueprint);

    expect($blueprint->hasField('ai_label'))->toBeFalse();
});

test('a blueprint that already defines ai_label is left untouched', function () {
    useCpAssetRoute();
    $blueprint = Blueprint::makeFromFields(['ai_label' => ['type' => 'text']]);

    AssetContainerBlueprintFound::dispatch($blueprint);

    expect($blueprint->fields()->get('ai_label')->config()['type'])->toBe('text');
});

test('injection listener is idempotent across repeated blueprint calls', function () {
    useCpAssetRoute();
    $blueprint = assetBlueprint();

    AssetContainerBlueprintFound::dispatch($blueprint);
    AssetContainerBlueprintFound::dispatch($blueprint);

    expect($blueprint->fields()->all())->toHaveCount(2)
        ->and($blueprint->hasField('ai_label'))->toBeTrue();
});

test('saving an asset in the CP persists the chosen label', function () {
    Queue::fake();

    app('router')->put('cp/assets/{encoded_asset}', function (Request $request, string $encoded_asset) {
        app(AssetsController::class)->update($request, $encoded_asset);

        return response('');
    })->name('statamic.cp.assets.update');

    $asset = AssetContainer::make('assets')->disk('public')->makeAsset('photo.jpg');
    Asset::shouldReceive('find')->once()->with($asset->id())->andReturn($asset);
    Asset::shouldReceive('make')->andReturnUsing(fn () => new Statamic\Assets\Asset);
    Asset::shouldReceive('save')->once();
    Asset::shouldReceive('all')->andReturn(collect());
    Gate::before(fn () => true);

    $this->actingAs(User::make()->id('tester')->email('tester@example.test')->makeSuper())
        ->withoutMiddleware()
        ->putJson('/cp/assets/'.base64_encode($asset->id()), ['ai_label' => 'generated'])
        ->assertOk();

    expect($asset->get('ai_label'))->toBe('generated');
});

test('unknown stored label values are treated as unlabelled', function () {
    expect(ServiceProvider::label('unexpected'))->toBeNull()
        ->and(ServiceProvider::label('generated'))->toBe('generated');
});

test('boot warns loudly when a configured badge image is missing', function () {
    config()->set('ai_labels.badges', ['generated' => 'missing-badge.png']);
    Log::spy();

    (new ServiceProvider(app()))->checkBadgeHealth();

    Log::shouldHaveReceived('warning')->once()->with('AI Labels badge image is missing: missing-badge.png');
});
