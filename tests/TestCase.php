<?php

namespace Rapidez\StatamicQueryBuilder\Tests;

use Rapidez\StatamicQueryBuilder\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
