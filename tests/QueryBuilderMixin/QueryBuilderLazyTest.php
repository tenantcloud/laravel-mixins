<?php

namespace Tests\QueryBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use TenantCloud\Mixins\QueryBuilderMixin;
use Tests\TestCase;

/** @see QueryBuilderMixin::lazy() */
class QueryBuilderLazyTest extends TestCase
{
	use RefreshDatabase;

	public function testReturnsLazy(): void
	{
		$lazy = DB::table('test_stubs')
			->orderBy('id')
			->lazy();

		// No need to test any more as this was backported and is covered with tests by Laravel itself.
		/* @phpstan-ignore-next-line */
		self::assertInstanceOf(LazyCollection::class, $lazy);
	}
}
