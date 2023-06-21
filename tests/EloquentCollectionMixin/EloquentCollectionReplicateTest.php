<?php

namespace Tests\EloquentCollectionMixin;

use Generator;
use Illuminate\Database\Eloquent\Collection;
use TenantCloud\Mixins\Mixins\EloquentCollectionMixin;
use Tests\Database\Models\TestStub;
use Tests\TestCase;

/**
 * @see EloquentCollectionMixin::replicate()
 */
class EloquentCollectionReplicateTest extends TestCase
{
	/**
	 * @dataProvider replicateProvider
	 */
	public function testReplicate(array $expected, Collection $collection, array $except = null): void
	{
		$result = $collection->replicate($except);

		$this->assertEquals($expected, $result->toArray());
	}

	public function replicateProvider(): Generator
	{
		yield 'empty' => [
			[],
			new Collection(),
		];

		yield 'without except' => [
			[
				[
					'name' => 'qwe',
				],
				[
					'name' => 'rty',
				],
			],
			new Collection([
				(new TestStub())->forceFill([
					'id'   => 123,
					'name' => 'qwe',
				]),
				(new TestStub())->forceFill([
					'name' => 'rty',
				]),
			]),
		];

		yield 'with except' => [
			[
				[
					'name' => 'qwe',
				],
				[
					'name' => 'rty',
				],
			],
			new Collection([
				(new TestStub())->forceFill([
					'id'    => 123,
					'name'  => 'qwe',
					'other' => 456,
				]),
				(new TestStub())->forceFill([
					'name'  => 'rty',
					'other' => 456,
				]),
			]),
			['other'],
		];
	}
}
