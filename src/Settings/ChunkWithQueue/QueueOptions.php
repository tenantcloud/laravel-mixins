<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use TenantCloud\Mixins\Jobs\GenerateChunksJob;

class QueueOptions
{
	/** Queue for generate chunk jobs. */
	public ?string $pieceQueue = null;

	/** Queue for chunk jobs. */
	public ?string $chunkQueue = null;

	/** The number of seconds between @see GenerateChunksJob handle */
	public ?int $delay;

	public function __construct(?string $pieceQueue, ?string $chunkQueue, int $delay = null)
	{
		$this->pieceQueue = $pieceQueue;
		$this->chunkQueue = $chunkQueue;
		$this->delay = $delay;
	}

	public static function defaultInstance(): self
	{
		return new self(null, null);
	}
}
