<?php


namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use TenantCloud\Providers\MixinsServiceProvider;


class TestCase extends BaseTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->app->register(MixinsServiceProvider::class);
	}
}
