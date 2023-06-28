<?php

namespace TenantCloud\Mixins\Queue\Handlers\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 */
interface QueuedChunkHandler
{
	/**
	 * @param Collection<int, TModel> $items
	 */
	public function handle(Collection $items): void;
}
