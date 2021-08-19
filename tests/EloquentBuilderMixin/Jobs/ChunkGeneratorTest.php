<?php

namespace Tests\EloquentBuilderMixin\Jobs;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use TenantCloud\Jobs\ChunkGenerator;
use TenantCloud\Jobs\ChunkParams;
use TenantCloud\Jobs\ChunkWorker;
use TenantCloud\Jobs\SerializableBuilder;
use TenantCloud\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\TestCase;

/**
 * @see ChunkGenerator
 */
class ChunkGeneratorTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp(): void
	{
		parent::setUp();

		$this->runDatabaseMigrations();
	}

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
