<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Jobs\HandleChunkJob;

class ChunkOptions
{
	public function __construct(
		/** Number of items that included into single chunk job. */
		public int $chunkSize,
		/** Number of items that included into generate chunk jobs job. */
		public int $pieceSize
	) {}

	public static function defaultInstance(): self
	{
		return new self(HandleChunkJob::CHUNK_SIZE, GenerateChunksJob::CHUNK_PIECE);
	}
}
