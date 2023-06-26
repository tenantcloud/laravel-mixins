<?php

namespace TenantCloud\Mixins\Queue\Handlers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;

use function dispatch;

/**
 * @template TModel of Model
 *
 * @template-implements QueuedChunkHandler<TModel>
 */
class SimpleQueuedChunkHandler implements QueuedChunkHandler
{
	/**
	 * @param QueuedItemHandler<TModel>|callable(TModel): mixed $itemHandler
	 */
	public function __construct(
		private readonly mixed $itemHandler
	) {
	}

	public function handle(Collection $items): void
	{
		foreach ($items as $item) {
			dispatch(
				$this->itemHandler instanceof QueuedItemHandler
						? $this->itemHandler->setItem($item)
						: ($this->itemHandler)($item)
			);
		}
	}
}
