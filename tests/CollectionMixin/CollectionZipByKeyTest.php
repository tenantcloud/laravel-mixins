<?php

namespace Tests\CollectionMixin;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use TenantCloud\Mixins\Mixins\CollectionMixin;
use Tests\TestCase;

/**
 * @see CollectionMixin::zipByKey()
 */
class CollectionZipByKeyTest extends TestCase
{
	#[DataProvider('zipsByKeyProvider')]
	public function testZipsByKey(Collection $expected, Collection $first, Collection $second): void
	{
		$this->assertSame(
			$expected->toArray(),
			$first->zipByKey($second)->toArray()
		);
	}

	public static function zipsByKeyProvider(): iterable
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
