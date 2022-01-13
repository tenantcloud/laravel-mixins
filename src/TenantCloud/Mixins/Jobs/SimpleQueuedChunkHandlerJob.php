<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Support\Collection;
use TenantCloud\Mixins\Settings\ChunkWithQueue\HandlerOptions;

class SimpleQueuedChunkHandlerJob implements QueuedChunkHandler
{
	protected HandlerOptions $itemHandler;

	public function __construct(HandlerOptions $itemHandler)
	{
		$this->itemHandler = $itemHandler;
	}

	public function handle(Collection $items)
	{
		foreach ($items as $item) {
			dispatch(
				with(new $this->itemHandler->handlerClass($item, ...$this->itemHandler->handlerParams), function ($job) {
					return method_exists($job, 'onQueue')
						? $job->onQueue($this->itemHandler->queue)
						: $job;
				})
			);
		}
	}
}
