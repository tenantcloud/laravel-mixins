<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TenantCloud\Mixins\MixinsServiceProvider;

class TestCase extends BaseTestCase
{
	use WithFaker;

	protected function setUp(): void
	{
		parent::setUp();

		$this->app->register(MixinsServiceProvider::class);
	}
}
