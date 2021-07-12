<?php

namespace Tests\CollectionMixin;

use TenantCloud\Mixins\CollectionMixin;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @see CollectionMixin::ungroup
 */
class CollectionUngroupTest extends TestCase
{
	public function testSuccess(): void
	{
		$collection = $this->collection()->ungroup();

		$this->assertSame(
			$collection->toArray(),
			[
				[
					'name'  => 'John Doe',
					'city'  => 'Dallas',
					'state' => 'Texas',
					'size'  => [
						'height' => 72,
						'weight' => 210,
					],
				],
			]
		);
	}

	protected function collection(): Collection
	{
		return Collection::make([
			'John Doe' => [
				[
					'name'  => 'John Doe',
					'city'  => 'Dallas',
					'state' => 'Texas',
					'size'  => [
						'height' => 72,
						'weight' => 210,
					],
				],
			],
		]);
	}
}
