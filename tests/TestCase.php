<?php

namespace ElSchneider\StatamicAiLabels\Tests;

use ElSchneider\StatamicAiLabels\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
