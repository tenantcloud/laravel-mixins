<?php

namespace Tests\CollectionMixin;

use Illuminate\Support\Collection;
use TenantCloud\Mixins\Mixins\CollectionMixin;
use Tests\TestCase;

/**
 * @see CollectionMixin::sortByMulti
 */
class CollectionSortByMultiTest extends TestCase
{
	public function testSuccess(): void
	{
		$oldCollection = $this->collection();
		$newCollection = $oldCollection
			->sortByMulti([
				'state'       => 'ASC',
				'city'        => 'ASC',
				'size.height' => 'DESC',
				'size.weight' => 'DESC',
				'name'        => 'ASC',
			]);

		$keyMapping = [
			0 => 2,
			1 => 0,
			2 => 3,
			3 => 1,
			4 => 4,
		];

		foreach ($newCollection->toArray() as $key => $value) {
			$this->assertSame($value, $oldCollection->toArray()[$keyMapping[$key]]);
		}
	}

	protected function collection(): Collection
	{
		return Collection::make([
			[
				'name'  => 'John Doe',
				'city'  => 'Dallas',
				'state' => 'Texas',
				'size'  => [
					'height' => 72,
					'weight' => 210,
				],
			],
			[
				'name'  => 'Jane Doe',
				'city'  => 'Houston',
				'state' => 'Texas',
				'size'  => [
					'height' => 60,
					'weight' => 120,
				],
			],
			[
				'name'  => 'Sam Swanson',
				'city'  => 'Dallas',
				'state' => 'Texas',
				'size'  => [
					'height' => 72,
					'weight' => 274,
				],
			],
			[
				'name'  => 'Jeremy Simpson',
				'city'  => 'Dallas',
				'state' => 'Texas',
				'size'  => [
					'height' => 71,
					'weight' => 210,
				],
			],
			[
				'name'  => 'Lois Smith',
				'city'  => 'Seattle',
				'state' => 'Washington',
				'size'  => [
					'height' => 65,
					'weight' => 132,
				],
			],
		]);
	}
}
