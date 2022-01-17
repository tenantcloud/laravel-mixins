<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use Illuminate\Contracts\Queue\ShouldQueue;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use Webmozart\Assert\Assert;

class HandlerOptions
{
	protected const TYPE_CHUNK_HANDLER = 'chunk';

	protected const TYPE_ITEM_HANDLER = 'item';

	public string $handlerType = self::TYPE_CHUNK_HANDLER;

	/** Handler classname */
	public string $handlerClass;

	/** Handler constructor parameters */
	public array $handlerParams;

	/** Queue for itemHandler */
	public ?string $queue = null;

	public function __construct(string $handlerType, string $handlerClass, array $handlerParams)
	{
		$this->handlerType = $handlerType;
		$this->handlerClass = $handlerClass;
		$this->handlerParams = $handlerParams;

		Assert::classExists($handlerClass);
		Assert::isAOf($handlerClass, $this->isChunk() ? QueuedChunkHandler::class : ShouldQueue::class);

		if ($this->isChunk()) {
			Assert::notNull(app($handlerClass, $handlerParams));
		}
	}

	public static function chunkHandler(string $handlerClass, array $handlerParams = []): self
	{
		return new self(self::TYPE_CHUNK_HANDLER, $handlerClass, $handlerParams);
	}

	public static function itemHandler(string $handlerClass, array $handlerParams = []): self
	{
		return new self(self::TYPE_ITEM_HANDLER, $handlerClass, $handlerParams);
	}

	public function isChunk(): bool
	{
		return $this->handlerType === self::TYPE_CHUNK_HANDLER;
	}
}
