<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Illuminate\Queue\SerializableClosure;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use TenantCloud\Mixins\Services\CacheSerializer;
use Webmozart\Assert\Assert;

abstract class Handler
{
	protected $handler;

	/**
	 * @param QueuedItemHandler|QueuedChunkHandler|callable|string $handler
	 */
	public function __construct($handler)
	{
		if (is_callable($handler)) {
			$this->handler = new SerializableClosure($handler);
		} elseif (is_string($handler)) {
			Assert::classExists($handler);

			$this->handler = $handler;
		} else {
			$this->handler = $handler;
		}
	}

	public function __serialize(): array
	{
		return CacheSerializer::serialize($this->handler);
	}

	public function __unserialize(array $data): void
	{
		$this->handler = CacheSerializer::unserialize($data);
	}

	/**
	 * @return SerializableClosure|QueuedChunkHandler|QueuedItemHandler
	 */
	public function getHandler()
	{
		return is_string($this->handler) ? app($this->handler) : $this->handler;
	}
}
