<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use Webmozart\Assert\Assert;

/**
 * @template TModel of Model
 *
 * @template-extends Handler<TModel>
 */
class ChunkHandler extends Handler
{
	/** @var QueuedChunkHandler<TModel>|callable(TModel):mixed|class-string<QueuedChunkHandler<TModel>> */
	protected $handler;

	/**
	 * @param QueuedChunkHandler<TModel>|callable(TModel):mixed|class-string<QueuedChunkHandler<TModel>> $handler
	 */
	public function __construct($handler)
	{
		parent::__construct($handler);

		Assert::isAnyOf($this->handler, [QueuedChunkHandler::class, SerializableClosure::class]);
	}
}
