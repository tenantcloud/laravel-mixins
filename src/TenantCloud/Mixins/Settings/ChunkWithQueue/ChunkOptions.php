<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Jobs\HandleChunkJob;

class ChunkOptions
{
	/** Number of items that included into single chunk job. */
	public int $chunkSize;

	/** Number of items that included into generate chunk jobs job. */
	public int $pieceSize;

	public function __construct(int $chunkSize, int $pieceSize)
	{
		$this->chunkSize = $chunkSize;
		$this->pieceSize = $pieceSize;
	}

	public static function defaultInstance(): self
	{
		return new self(HandleChunkJob::CHUNK_SIZE, GenerateChunksJob::CHUNK_PIECE);
	}
}
