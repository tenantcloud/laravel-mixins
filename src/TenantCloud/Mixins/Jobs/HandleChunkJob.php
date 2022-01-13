<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use TenantCloud\Mixins\Settings\ChunkWithQueue\HandlerOptions;
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

	protected HandlerOptions $handler;

	protected string $key;

	protected SerializableBuilder $serializedBuilder;

	public function __construct(SerializableBuilder $serializedBuilder, string $key, array $itemIds, HandlerOptions $handler)
	{
		Assert::classExists($handler->handlerClass);
		Assert::isAOf($handler->handlerClass, $handler->isChunk() ? QueuedChunkHandler::class : ShouldQueue::class);

		$this->serializedBuilder = $serializedBuilder;
		$this->key = $key;
		$this->itemIds = $itemIds;
		$this->handler = $handler;
	}

	public function handle(): void
	{
		$builder = $this->serializedBuilder->getBuilder();
		$items = $builder->whereIn($this->key, $this->itemIds)->get();

		/* @var QueuedChunkHandler $handler */
		$handler = $this->handler->isChunk()
			? app($this->handler->handlerClass, $this->handler->handlerParams)
			: new SimpleQueuedChunkHandlerJob($this->handler);

		$handler->handle($items);
	}
}
