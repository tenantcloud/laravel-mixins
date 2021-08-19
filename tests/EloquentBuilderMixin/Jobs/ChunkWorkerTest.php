<?php

namespace Tests\EloquentBuilderMixin\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use TenantCloud\Mixins\Jobs\ChunkWorker;
use TenantCloud\Mixins\Jobs\SerializableBuilder;
use TenantCloud\Mixins\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @see ChunkWorker
 */
class ChunkWorkerTest extends TestCase
{
	use RefreshDatabase;

	public function testFireHandleWithOutItems(): void
	{
		$serializedBuilder = new SerializableBuilder(TestStub::query());

		$this->mock(HandlerStub::class, function (MockInterface $mock) {
			$mock->shouldReceive('handle')
				->with(Mockery::on(fn ($items) => $items instanceof Collection && $items->count() === 0))
				->once();
		});

		(new ChunkWorker($serializedBuilder, 'id', [], HandlerStub::class))->handle();
	}

	public function testFireHandleWithItems(): void
	{
		$model = new TestStub();
		$model->name = $this->faker->name;
		$model->save();

		$serializedBuilder = new SerializableBuilder(TestStub::query());

		$this->mock(HandlerStub::class, function (MockInterface $mock) use ($model) {
			$mock->shouldReceive('handle')
				->with(Mockery::on(fn ($items) => $items instanceof Collection && $items->count() === 1 && $model->id === $items->first()->id))
				->once();
		});

		(new ChunkWorker($serializedBuilder, 'id', [$model->id], HandlerStub::class))->handle();
	}

	public function testAssertNotExistingHandler(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$serializedBuilder = new SerializableBuilder(TestStub::query());

		(new ChunkWorker($serializedBuilder, 'id', [], 'App\Test'))->handle();
	}

	public function testAssertInvalidHandler(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$serializedBuilder = new SerializableBuilder(TestStub::query());

		(new ChunkWorker($serializedBuilder, 'id', [], TestStub::class))->handle();
	}
}
