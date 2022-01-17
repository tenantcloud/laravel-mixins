<?php

namespace TenantCloud\Mixins\Queue\Handlers\Contracts;

use Illuminate\Support\Collection;

interface QueuedChunkHandler
{
	public function handle(Collection $items);
}
