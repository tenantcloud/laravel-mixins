<?php

namespace Tests\EloquentBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Mixins\EloquentBuilderMixin;
use Tests\Database\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @see EloquentBuilderMixin::chunkWithQueue()
 */
class ChunkWithQueueTest extends TestCase
{
	use RefreshDatabase;

	public function testSingleModelSuccess(): void
	{
		Queue::fake();
		$this->generateTestModel();

		TestStub::query()->chunkWithQueue(HandlerStub::class);

		Queue::assertPushed(GenerateChunksJob::class, 1);
	}

	public function testMultipleJobsSuccess(): void
	{
		Queue::fake();

		$this->generateTestModel();
		$this->generateTestModel();

		TestStub::query()->chunkWithQueue(HandlerStub::class, 1, 1);

		Queue::assertPushed(GenerateChunksJob::class, 2);
	}

	public function testJobNotFiredIfNoItemsExisting(): void
	{
		Queue::fake();

		TestStub::query()->chunkWithQueue(HandlerStub::class, 1, 1);

		Queue::assertNotPushed(GenerateChunksJob::class);
	}

	public function testNotExistedHandlerFailure(): void
	{
		$this->expectException(InvalidArgumentException::class);
		TestStub::query()->chunkWithQueue('App\Test');
	}

	public function testInvalidHandlerFailure(): void
	{
		$this->expectException(InvalidArgumentException::class);
		TestStub::query()->chunkWithQueue(TestStub::class);
	}

	private function generateTestModel(): TestStub
	{
		$model = new TestStub();
		$model->name = $this->faker->name;
		$model->save();

		return $model;
	}
}
