<?php

namespace TenantCloud\Mixins\Queue\Handlers\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface QueuedItemHandler
{
	/**
	 * @param TModel $item
	 */
	public function setItem(Model $item): static;
}
