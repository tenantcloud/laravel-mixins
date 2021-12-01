<?php

namespace Tests\CollectionMixin;

use Generator;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Mixins\CollectionMixin;
use Tests\TestCase;

/**
 * @see CollectionMixin::zipByKey()
 */
class CollectionZipByKeyTest extends TestCase
{
	/**
	 * @dataProvider zipsByKeyProvider
	 */
	public function testZipsByKey(Collection $expected, Collection $first, Collection $second): void
	{
		$this->assertSame(
			$expected->toArray(),
			$first->zipByKey($second)->toArray()
		);
	}

	public function zipsByKeyProvider(): Generator
	{
		yield 'empty' => [
			new Collection(),
			new Collection(),
			new Collection(),
		];

		yield 'by index key' => [
			new Collection([
				[1, 4],
				[2, 5],
				[3, 6],
				[null, 7],
			]),
			new Collection([
				1,
				2,
				3,
			]),
			new Collection([
				4,
				5,
				6,
				7,
			]),
		];

		yield 'by string key' => [
			new Collection([
				[1, null],
				[2, 3],
				[null, 4],
			]),
			new Collection([
				'a' => 1,
				'b' => 2,
			]),
			new Collection([
				'b' => 3,
				'c' => 4,
			]),
		];
	}
}
