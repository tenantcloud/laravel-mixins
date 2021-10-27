<?php

namespace Tests\EloquentBuilderMixin\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use TenantCloud\Mixins\Jobs\HandleChunkJob;
use TenantCloud\Mixins\Jobs\SerializableBuilder;
use Tests\Database\Models\TestStub;
use Tests\EloquentBuilderMixin\Stubs\HandlerStub;
use Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @see HandleChunkJob
 */
class HandleChunkJobTest extends TestCase
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

		(new HandleChunkJob($serializedBuilder, 'id', [], HandlerStub::class))->handle();
	}

	public function testFireHandleWithItem(): void
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

		(new HandleChunkJob($serializedBuilder, 'id', [$model->id], HandlerStub::class))->handle();
	}

	public function testFireHandleWithMultipleItems(): void
	{
		$model = new TestStub();
		$model->name = $this->faker->name;
		$model->save();

		$model2 = new TestStub();
		$model2->name = $this->faker->name;
		$model2->save();

		$serializedBuilder = new SerializableBuilder(TestStub::query());

		$this->mock(HandlerStub::class, function (MockInterface $mock) {
			$mock->shouldReceive('handle')
				->with(Mockery::on(fn ($items) => $items instanceof Collection && $items->count() === 2))
				->once();
		});

		(new HandleChunkJob($serializedBuilder, 'id', [$model->id, $model2->id], HandlerStub::class))->handle();
	}

	public function testAssertNotExistingHandler(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$serializedBuilder = new SerializableBuilder(TestStub::query());

		(new HandleChunkJob($serializedBuilder, 'id', [], 'App\Test'))->handle();
	}

	public function testAssertInvalidHandler(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$serializedBuilder = new SerializableBuilder(TestStub::query());

		(new HandleChunkJob($serializedBuilder, 'id', [], TestStub::class))->handle();
	}
}
