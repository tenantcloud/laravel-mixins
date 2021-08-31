<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Illuminate\Support\Collection;
use TenantCloud\Mixins\Jobs\QueuedChunkHandler;

class HandlerStub implements QueuedChunkHandler
{
	public function handle(Collection $items): void
	{
	}
}
