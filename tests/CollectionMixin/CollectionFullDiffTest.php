<?php

namespace Tests\CollectionMixin;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use TenantCloud\Mixins\Mixins\CollectionMixin;
use Tests\TestCase;

/**
 * @see CollectionMixin::fullDiff()
 */
class CollectionFullDiffTest extends TestCase
{
	#[DataProvider('fullDiffProvider')]
	public function testFullDiff(array $expected, Collection $first, Collection $second): void
	{
		[$onlyFirst, $both, $onlySecond] = $first->fullDiff(
			$second,
			fn (array $item) => $item['key'],
			fn (array $item) => $item['foreign_key']
		);

		$this->assertEquals(
			[
				$expected[0]->toArray(),
				$expected[1]->toArray(),
				$expected[2]->toArray(),
			],
			[
				$onlyFirst->toArray(),
				$both->toArray(),
				$onlySecond->toArray(),
			]
		);
	}

	public static function fullDiffProvider(): iterable
	{
		yield 'empty' => [
			[
				new Collection(),
				new Collection(),
				new Collection(),
			],
			new Collection(),
			new Collection(),
		];

		yield 'only matches' => [
			[
				new Collection(),
				new Collection([
					[
						['key' => 22],
						['foreign_key' => 22],
					],
					[
						['key' => 33],
						['foreign_key' => 33],
					],
				]),
				new Collection(),
			],
			new Collection([
				['key' => 22],
				['key' => 33],
			]),
			new Collection([
				['foreign_key' => 22],
				['foreign_key' => 33],
			]),
		];

		yield 'matches from both sides' => [
			[
				new Collection([
					['key' => 22],
				]),
				new Collection([
					[
						['key' => 33],
						['foreign_key' => 33],
					],
				]),
				new Collection([
					['foreign_key' => 44],
				]),
			],
			new Collection([
				['key' => 22],
				['key' => 33],
			]),
			new Collection([
				['foreign_key' => 33],
				['foreign_key' => 44],
			]),
		];
	}
}
