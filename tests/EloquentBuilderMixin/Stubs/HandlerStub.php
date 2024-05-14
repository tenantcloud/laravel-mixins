<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Illuminate\Support\Collection;
use Laravel\SerializableClosure\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use Tests\Database\Models\TestStub;

/**
 * @template-implements QueuedChunkHandler<TestStub>
 */
class HandlerStub implements QueuedChunkHandler
{
	protected ?SerializableClosure $callback;

	public function __construct(?callable $callback = null)
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
