<?php

namespace Tests\EloquentBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\LazyCollection;
use TenantCloud\Mixins\QueryBuilderMixin;
use Tests\Database\Models\TestStub;
use Tests\TestCase;

/** @see QueryBuilderMixin::lazy() */
class EloquentBuilderLazyTest extends TestCase
{
	use RefreshDatabase;

	public function testReturnsLazy(): void
	{
		$lazy = TestStub::query()
			->orderBy('id')
			->lazy();

		// No need to test any more as this was backported and is covered with tests by Laravel itself.
		self::assertInstanceOf(LazyCollection::class, $lazy);
	}
}
