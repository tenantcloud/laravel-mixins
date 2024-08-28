<?php

namespace Tests\QueryBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TenantCloud\Mixins\Mixins\QueryBuilderMixin;
use Tests\Database\Factories\TestStubFactory;
use Tests\Database\Models\TestStub;
use Tests\TestCase;

/** @see QueryBuilderMixin::conditionalUpdate() */
class QueryBuilderConditionalUpdateTest extends TestCase
{
	use RefreshDatabase;

	public function testUpdatesRecordsUsingAMap(): void
	{
		$stub1 = TestStubFactory::new()->create([
			'name' => 'one',
		]);
		$stub2 = TestStubFactory::new()->create([
			'name'        => 'two',
			'description' => 'unchanged',
		]);
		$stub3 = TestStubFactory::new()->create([
			'name' => 'three',
		]);

		TestStub::query()
			->getQuery()
			->conditionalUpdate('description', 'name', [
				'one'   => 'one description',
				'three' => 'three description',
			]);

		self::assertSame('one description', $stub1->fresh()->description);
		self::assertSame('unchanged', $stub2->fresh()->description);
		self::assertSame('three description', $stub3->fresh()->description);
	}
}
