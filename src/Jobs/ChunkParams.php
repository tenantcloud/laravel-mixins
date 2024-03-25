<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Database\Eloquent\Model;
use TenantCloud\Mixins\Queue\Handlers\Serializable\Handler;

/**
 * @template TModel of Model
 */
class ChunkParams
{
	/** @var Handler<TModel> */
	public Handler $handler;

	public string $key;

	public string $keyAttributeName;

	public int $chunkSize;

	public int $pieceSize;

	public ?string $queue;

	/**
	 * @param Handler<TModel> $handler
	 */
	public function __construct(
		Handler $handler,
		string $key,
		string $keyAttributeName,
		int $chunkSize,
		int $pieceSize,
		string $queue = null
	) {
		$this->handler = $handler;
		$this->key = $key;
		$this->keyAttributeName = $keyAttributeName;
		$this->chunkSize = $chunkSize;
		$this->pieceSize = $pieceSize;
		$this->queue = $queue;
	}
}
