<?php

namespace Tests\QueryBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use TenantCloud\Mixins\QueryBuilderMixin;
use Tests\TestCase;

/** @see QueryBuilderMixin::customWhereNested() */
class QueryBuilderCustomWhereNestedTest extends TestCase
{
	use RefreshDatabase;

	public function testAddsJoinsAndNestedWheres(): void
	{
		$query = DB::table('test_stubs')
			->orderBy('id')
			->where('field', '=', 123)
			->customWhereNested(function ($query) {
				$query->join('other_table', 'other_table.id', '=', 'test_stubs.id')
					->where('other', 456)
					->orWhere('else', 789);
			});

		self::assertSame(
			'select * from "test_stubs" inner join "other_table" on "other_table"."id" = "test_stubs"."id" where "field" = ? and ("other" = ? or "else" = ?) order by "id" asc',
			$query->toSql(),
		);
		self::assertSame([123, 456, 789], $query->getBindings());
	}
}
