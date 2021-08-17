<?php

namespace TenantCloud\Jobs;

use Illuminate\Support\Collection;

interface ChunkWorkerContract
{
	public function handle(Collection $items);
}
