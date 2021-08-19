<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use TenantCloud\MixinsServiceProvider;

class TestCase extends BaseTestCase
{
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
