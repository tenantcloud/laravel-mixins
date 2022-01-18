<?php

namespace TenantCloud\Mixins\Queue\Handlers\Contracts;

use Illuminate\Database\Eloquent\Model;

interface QueuedItemHandler
{
	public function setItem(Model $item): self;
}
