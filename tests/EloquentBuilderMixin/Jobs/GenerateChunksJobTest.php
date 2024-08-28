<?php

namespace Tests\EloquentBuilderMixin\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use TenantCloud\Mixins\Jobs\ChunkParams;
use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Jobs\HandleChunkJob;
use TenantCloud\Mixins\Jobs\SerializableBuilder;
use TenantCloud\Mixins\Queue\Handlers\Serializable\ChunkHandler;
use Tests\Database\Factories\TestStubFactory;
use Tests\Database\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\TestCase;

/**
 * @see GenerateChunksJob
 */
class GenerateChunksJobTest extends TestCase
{
	use RefreshDatabase;

	public function testChunkWorkerDoesNotFireIfNoItemsExists(): void
	{
		Queue::fake();

		$serializedBuilder = new SerializableBuilder(TestStub::query());
		$params = new ChunkParams(
			new ChunkHandler(HandlerStub::class),
			'id',
			'id',
			1,
			1
		);

		(new GenerateChunksJob($serializedBuilder, $params, 1))->handle();

		Queue::assertNotPushed(HandleChunkJob::class);
	}

	public function testCalculateMinMaxPrimaryKeyValue(): void
	{
		Queue::fake();

		TestStubFactory::new()->create();

		$serializedBuilder = new SerializableBuilder(TestStub::query());
		$params = new ChunkParams(
			new ChunkHandler(HandlerStub::class),
			'id',
			'id',
			1,
			1
		);

		(new GenerateChunksJob($serializedBuilder, $params, 1))->handle();

		Queue::assertPushed(HandleChunkJob::class, 1);
	}
}
