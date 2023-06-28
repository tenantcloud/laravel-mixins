<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use Webmozart\Assert\Assert;

/**
 * @template TModel of Model
 *
 * @template-extends Handler<TModel>
 */
class ItemHandler extends Handler
{
	/** @var QueuedItemHandler<TModel>|callable(TModel):mixed|class-string<QueuedItemHandler<TModel>> */
	protected $handler;

	/**
	 * @param QueuedItemHandler<TModel>|callable(TModel):mixed|class-string<QueuedItemHandler<TModel>> $handler
	 */
	public function __construct($handler)
	{
		parent::__construct($handler);

		Assert::isAnyOf($this->handler, [QueuedItemHandler::class, SerializableClosure::class]);
	}
}
