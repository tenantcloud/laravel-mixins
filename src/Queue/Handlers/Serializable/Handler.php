<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use TenantCloud\Mixins\Services\CacheSerializer;
use Webmozart\Assert\Assert;

/**
 * @template TModel of Model
 */
abstract class Handler
{
	/** @var QueuedItemHandler<TModel>|QueuedChunkHandler<TModel>|callable(TModel):mixed|class-string<QueuedChunkHandler<TModel>|QueuedItemHandler<TModel>> */
	protected $handler;

	/**
	 * @param QueuedItemHandler<TModel>|QueuedChunkHandler<TModel>|callable(TModel):mixed|class-string<QueuedChunkHandler<TModel>|QueuedItemHandler<TModel>> $handler
	 */
	public function __construct($handler)
	{
		if (is_callable($handler)) {
			$this->handler = new SerializableClosure($handler);
		} elseif (is_string($handler)) {
			/* @phpstan-ignore-next-line */
			Assert::classExists($handler);

			$this->handler = $handler;
		} else {
			$this->handler = $handler;
		}
	}

	/**
	 * @return callable(TModel):mixed|QueuedChunkHandler<TModel>|QueuedItemHandler<TModel>
	 */
	public function getHandler()
	{
		return is_string($this->handler) ? app($this->handler) : $this->handler;
	}

	public function __serialize(): array
	{
		return CacheSerializer::serialize($this->handler);
	}

	/**
	 * @param array{ key: string } $data
	 */
	public function __unserialize(array $data): void
	{
		/* @phpstan-ignore-next-line */
		$this->handler = CacheSerializer::unserialize($data);
	}
}
