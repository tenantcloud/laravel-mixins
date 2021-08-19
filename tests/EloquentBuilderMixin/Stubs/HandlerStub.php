<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Illuminate\Support\Collection;
use TenantCloud\Mixins\Jobs\ChunkWorkerContract;

class HandlerStub implements ChunkWorkerContract
{
	public function handle(Collection $items): void
	{
	}
}
