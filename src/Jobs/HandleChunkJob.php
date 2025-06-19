<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
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
 * @template TModel of Model
 *
 * @see HandleChunkJobTest
 */
class HandleChunkJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_SIZE = 15;

	/**
	 * @param SerializableBuilder<TModel> $serializedBuilder
	 * @param list<int|string>            $itemIds
	 * @param Handler<TModel>             $handler
	 */
	public function __construct(
		private readonly SerializableBuilder $serializedBuilder,
		private readonly string $key,
		private readonly array $itemIds,
		private readonly Handler $handler
	) {
		Assert::isAnyOf($handler->getHandler(), [SerializableClosure::class, QueuedChunkHandler::class, QueuedItemHandler::class]);
	}

	public function handle(): void
	{
		$builder = $this->serializedBuilder->builder;
		$items = $builder->whereIn($this->key, $this->itemIds)->get();

		$handler = $this->handler instanceof ChunkHandler
			? $this->handler->getHandler()
			: new SimpleQueuedChunkHandler($this->handler->getHandler());

		if (is_callable($handler)) {
			$handler($items);
		} else {
			/* @phpstan-ignore-next-line Refactor handlers to fix these */
			$handler->handle($items);
		}
	}
}
