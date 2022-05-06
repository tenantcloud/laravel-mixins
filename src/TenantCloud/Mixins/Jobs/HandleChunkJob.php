<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use TenantCloud\Mixins\Queue\Handlers\Serializable\ChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Serializable\Handler;
use TenantCloud\Mixins\Queue\Handlers\SimpleQueuedChunkHandler;
use Tests\EloquentBuilderMixin\Jobs\HandleChunkJobTest;
use Webmozart\Assert\Assert;

/**
 * @see HandleChunkJobTest
 */
class HandleChunkJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_SIZE = 15;

	protected array $itemIds;

	protected Handler $handler;

	protected string $key;

	protected SerializableBuilder $serializedBuilder;

	public function __construct(SerializableBuilder $serializedBuilder, string $key, array $itemIds, Handler $handler)
	{
		Assert::isAnyOf($handler->getHandler(), [SerializableClosure::class, QueuedChunkHandler::class, QueuedItemHandler::class]);

		$this->serializedBuilder = $serializedBuilder;
		$this->key = $key;
		$this->itemIds = $itemIds;
		$this->handler = $handler;
	}

	public function handle(): void
	{
		$builder = $this->serializedBuilder->getBuilder();
		$items = $builder->whereIn($this->key, $this->itemIds)->get();

		$handler = $this->handler instanceof ChunkHandler
			? $this->handler->getHandler()
			: new SimpleQueuedChunkHandler($this->handler->getHandler());

		if (is_callable($handler)) {
			$handler($items);
		} else {
			$handler->handle($items);
		}
	}
}
