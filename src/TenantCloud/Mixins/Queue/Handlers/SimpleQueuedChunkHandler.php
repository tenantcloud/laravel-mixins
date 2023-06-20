<?php

namespace TenantCloud\Mixins\Queue\Handlers;

use Closure;

use function dispatch;

use Illuminate\Support\Collection;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;

class SimpleQueuedChunkHandler implements QueuedChunkHandler
{
	/** @var QueuedItemHandler|Closure */
	protected $itemHandler;

	public function __construct($handler)
	{
		$this->itemHandler = $handler;
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
