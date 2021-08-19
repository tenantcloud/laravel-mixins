<?php

namespace Tests\EloquentBuilderMixin\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use TenantCloud\Mixins\Jobs\ChunkGenerator;
use TenantCloud\Mixins\Jobs\ChunkParams;
use TenantCloud\Mixins\Jobs\ChunkWorker;
use TenantCloud\Mixins\Jobs\SerializableBuilder;
use TenantCloud\Mixins\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\TestCase;

/**
 * @see ChunkGenerator
 */
class ChunkGeneratorTest extends TestCase
{
	use RefreshDatabase;

	public function testChunkWorkerDoesNotFireIfNoItemsExists(): void
	{
		Queue::fake();

		$serializedBuilder = new SerializableBuilder(TestStub::query());
		$params = new ChunkParams(
			HandlerStub::class,
			'id',
			1,
			1
		);

		(new ChunkGenerator($serializedBuilder, $params, 1))->handle();

		Queue::assertNotPushed(ChunkWorker::class);
	}

	public function testCalculateMinMaxPrimaryKeyValue(): void
	{
		Queue::fake();
		$model = new TestStub();
		$model->name = $this->faker->name;
		$model->save();

		$serializedBuilder = new SerializableBuilder(TestStub::query());
		$params = new ChunkParams(
			HandlerStub::class,
			'id',
			1,
			1
		);

		(new ChunkGenerator($serializedBuilder, $params, 1))->handle();

		Queue::assertPushed(ChunkWorker::class, 1);
	}
}
