<?php

namespace TenantCloud\Jobs;

interface ChunkWorkerContract
{
	public function __construct(string $minKeyValue, string $maxKeyValue);
}
