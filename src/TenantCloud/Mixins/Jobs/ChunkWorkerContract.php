<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Support\Collection;

interface ChunkWorkerContract
{
	public function handle(Collection $items);
}
