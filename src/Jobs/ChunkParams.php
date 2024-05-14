<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Database\Eloquent\Model;
use TenantCloud\Mixins\Queue\Handlers\Serializable\Handler;

/**
 * @template TModel of Model
 */
class ChunkParams
{
	/**
	 * @param Handler<TModel> $handler
	 */
	public function __construct(
		/** @var Handler<TModel> */
		public Handler $handler,
		public string $key,
		public string $keyAttributeName,
		public int $chunkSize,
		public int $pieceSize,
		public ?string $queue = null
	) {}
}
