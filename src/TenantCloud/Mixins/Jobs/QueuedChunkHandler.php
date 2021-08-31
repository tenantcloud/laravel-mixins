<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Support\Collection;

interface QueuedChunkHandler
{
	public function handle(Collection $items);
}
