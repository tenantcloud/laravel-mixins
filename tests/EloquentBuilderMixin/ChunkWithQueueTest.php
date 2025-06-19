<?php

namespace Tests\EloquentBuilderMixin;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Mixins\EloquentBuilderMixin;
use TenantCloud\Mixins\Queue\Handlers\Serializable\ItemHandler;
use TenantCloud\Mixins\Settings\ChunkWithQueue\ChunkWithQueueSettings;
use TenantCloud\Mixins\Settings\ChunkWithQueue\QueryOptions;
use Tests\Database\Factories\TestStubFactory;
use Tests\Database\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerWithConstructorStub;
use Tests\EloquentBuilderMixin\Stubs\SimpleJob;
use Tests\EloquentBuilderMixin\Stubs\SimpleJobWithoutParams;
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

	public function testHandlerParametersPassed(): void
	{
		Carbon::setTestNow('2022-07-20 12:00:00');

		$this->generateTestModel();

		TestStub::query()->chunkWithQueue(new HandlerWithConstructorStub(now()), ChunkWithQueueSettings::defaultSettings());

		self::assertNotNull(HandlerWithConstructorStub::$calledThis);
		self::assertEquals(now(), HandlerWithConstructorStub::$calledThis->date);
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

	public function testWithCallableHandlerSuccess(): void
	{
		$model = $this->generateTestModel();

		$name = $this->faker->name;
		$time = new Carbon($this->faker->dateTime);

		TestStub::query()->chunkWithQueue(new ItemHandler(new SimpleJob($name, $time)));

		$this->assertSame($name, $model->refresh()->name);
		$this->assertSame($time->toDateTimeString(), $model->updated_at->toDateTimeString());
	}

	public function testWithSimpleChunkHandlerWithoutParamsSuccess(): void
	{
		$model1 = $this->generateTestModel();
		$model2 = $this->generateTestModel();

		TestStub::query()->chunkWithQueue(new ItemHandler(static fn (TestStub $item) => new SimpleJobWithoutParams($item)));

		foreach ([$model1, $model2] as $model) {
			$this->assertSame('Job working', $model->refresh()->name);
		}
	}

	public function testMultipleJobsWithTableNameInQuerySettingsSuccess(): void
	{
		Queue::fake();

		$this->generateTestModel();
		$this->generateTestModel();

		$settings = ChunkWithQueueSettings::defaultSettings();
		$settings->chunkOptions->chunkSize = 1;
		$settings->chunkOptions->pieceSize = 1;
		$settings->queryOptions = new QueryOptions((new TestStub())->qualifyColumn('id'));

		TestStub::query()->chunkWithQueue(HandlerStub::class, $settings);

		Queue::assertPushed(GenerateChunksJob::class, 2);
	}

	public function testGenerateJobsForWholeQuery(): void
	{
		Queue::fake();

		$this->generateTestModel();
		$this->generateTestModel(fn (TestStubFactory $factory) => $factory->hasChildren(TestStubFactory::new()));
		$this->generateTestModel();

		$settings = ChunkWithQueueSettings::defaultSettings();
		$settings->chunkOptions->chunkSize = 1;
		$settings->chunkOptions->pieceSize = 1;
		$settings->queryOptions = QueryOptions::defaultInstanceFromWholeTableQuery();

		TestStub::query()->whereHas('children')->chunkWithQueue(HandlerStub::class, $settings);

		Queue::assertPushed(GenerateChunksJob::class, 4);
	}

	public function testWithMaxIdFromGivenQuery(): void
	{
		Queue::fake();

		$this->generateTestModel();
		$this->generateTestModel(fn (TestStubFactory $factory) => $factory->hasChildren(TestStubFactory::new()));
		$this->generateTestModel();

		$settings = ChunkWithQueueSettings::defaultSettings();
		$settings->chunkOptions->chunkSize = 1;
		$settings->chunkOptions->pieceSize = 1;
		$settings->queryOptions = QueryOptions::defaultInstance();

		TestStub::query()->whereHas('children')->chunkWithQueue(HandlerStub::class, $settings);

		Queue::assertPushed(GenerateChunksJob::class, 1);
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

	public function testNotIncorrectHandlerType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		TestStub::query()->chunkWithQueue(new ItemHandler(new SimpleJobWithoutParams(new TestStub())));
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

	/**
	 * @param callable(TestStubFactory):TestStubFactory|null $callback
	 */
	private function generateTestModel(?callable $callback = null): TestStub
	{
		return with(TestStubFactory::new(), $callback)->create();
	}
}
