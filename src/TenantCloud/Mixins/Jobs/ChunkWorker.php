<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tests\EloquentBuilderMixin\Jobs\ChunkWorkerTest;
use Webmozart\Assert\Assert;

/**
 * @see ChunkWorkerTest
 */
class ChunkWorker implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_SIZE = 15;

	protected array $itemIds;

	protected string $handler;

	protected string $key;

	protected SerializableBuilder $serializedBuilder;

	public function __construct(SerializableBuilder $serializedBuilder, string $key, array $itemIds, string $handler)
	{
		Assert::classExists($handler);
		Assert::isAOf($handler, ChunkWorkerContract::class);

		$this->serializedBuilder = $serializedBuilder;
		$this->key = $key;
		$this->itemIds = $itemIds;
		$this->handler = $handler;
	}

	public function handle(): void
	{
		$builder = $this->serializedBuilder->getBuilder();
		$items = $builder->where($this->key, $this->itemIds)->get();

		/* @var ChunkWorkerContract $handler */
		$handler = app($this->handler);

		$handler->handle($items);
	}
}
