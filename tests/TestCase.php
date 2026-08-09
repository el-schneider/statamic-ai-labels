<?php

namespace ElSchneider\StatamicAiLabels\Tests;

use ElSchneider\StatamicAiLabels\ServiceProvider;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function deleteFakeStacheDirectory(): void
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);
    }
}
