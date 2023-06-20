<?php

namespace Tests\QueryBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use TenantCloud\Mixins\QueryBuilderMixin;
use Tests\TestCase;

/** @see QueryBuilderMixin::whereSubOther() */
class QueryBuilderWhereSubOtherTest extends TestCase
{
	use RefreshDatabase;

	public function testColumnEqualsSubQuery(): void
	{
		$query = DB::table('test_stubs')
			->whereSubOther('some_id', '=', DB::table('something_else')->select('id')->where('field', 123)->limit(1));

		self::assertSame(
			'select * from "test_stubs" where "some_id" = (select "id" from "something_else" where "field" = ? limit 1)',
			$query->toSql(),
		);
		self::assertSame([123], $query->getBindings());
	}
}
