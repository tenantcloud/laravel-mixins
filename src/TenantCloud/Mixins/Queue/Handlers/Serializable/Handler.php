<?php

namespace TenantCloud\Mixins\Queue\Handlers\Serializable;

use Carbon\Carbon;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Facades\Cache;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use Webmozart\Assert\Assert;

abstract class Handler
{
	protected $handler;

	protected string $key;

	/**
	 * @param QueuedItemHandler|QueuedChunkHandler|callable|string $handler
	 */
	public function __construct($handler)
	{
		if (is_callable($handler)) {
			$this->handler = new SerializableClosure($handler);
		} elseif (is_string($handler)) {
			Assert::classExists($handler);

			$this->handler = new $handler();
		} else {
			$this->handler = $handler;
		}

		$this->key = uniqid('chunk_job:', false);
		Cache::put($this->key, serialize($this->handler), Carbon::now()->addWeek());
	}

	public function __serialize(): array
	{
		return ['key' => $this->key];
	}

	public function __unserialize(array $data): void
	{
		$this->key = $data['key'];

		$this->handler = unserialize(Cache::get($this->key));
	}

	/**
	 * @return SerializableClosure|QueuedChunkHandler|QueuedItemHandler
	 */
	public function getHandler()
	{
		return $this->handler;
	}
}
