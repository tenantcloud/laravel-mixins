<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;

class HandlerStub implements QueuedChunkHandler
{
	protected ?SerializableClosure $callback;

	public function __construct(callable $callback = null)
	{
		$this->callback = $callback ? new SerializableClosure($callback) : null;
	}

	public function handle(Collection $items): void
	{
		if ($this->callback) {
			($this->callback)($items);
		}
	}
}
