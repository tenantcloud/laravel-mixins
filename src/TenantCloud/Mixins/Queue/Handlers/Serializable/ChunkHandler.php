<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Illuminate\Queue\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use Webmozart\Assert\Assert;

class ChunkHandler extends Handler
{
	/** @var QueuedChunkHandler|SerializableClosure */
	protected $handler;

	public function __construct($handler)
	{
		parent::__construct($handler);

		Assert::isAnyOf($this->handler, [QueuedChunkHandler::class, SerializableClosure::class]);
	}
}
