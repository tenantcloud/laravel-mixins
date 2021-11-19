<?php

namespace Tests\EloquentBuilderMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Mixins\EloquentBuilderMixin;
use TenantCloud\Mixins\Settings\ChunkWithQueue\ChunkWithQueueSettings;
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

		$settings = ChunkWithQueueSettings::defaultSettings();
		$settings->chunkOptions->chunkSize = 1;
		$settings->chunkOptions->pieceSize = 1;

		TestStub::query()->chunkWithQueue(HandlerStub::class, $settings);

		Queue::assertPushed(GenerateChunksJob::class, 2);
	}

	public function testJobNotFiredIfNoItemsExisting(): void
	{
		Queue::fake();

		$settings = ChunkWithQueueSettings::defaultSettings();
		$settings->chunkOptions->chunkSize = 1;
		$settings->chunkOptions->pieceSize = 1;

		TestStub::query()->chunkWithQueue(HandlerStub::class, $settings);

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
