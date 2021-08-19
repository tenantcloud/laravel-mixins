<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TenantCloud\Mixins\MixinsServiceProvider;

class TestCase extends BaseTestCase
{
	use WithFaker;

	/**
	 * {@inheritDoc}
	 */
	protected function getPackageProviders($app): array
	{
		return [
			MixinsServiceProvider::class,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function defineDatabaseMigrations(): void
	{
		$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
	}
}
