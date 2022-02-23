<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Laravel\SerializableClosure\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use Webmozart\Assert\Assert;

class ItemHandler extends Handler
{
	/** @var QueuedItemHandler|SerializableClosure|string */
	protected $handler;

	/**
	 * @param QueuedItemHandler|callable|string $handler
	 */
	public function __construct($handler)
	{
		parent::__construct($handler);

		Assert::isAnyOf($this->handler, [QueuedItemHandler::class, SerializableClosure::class]);
	}
}
