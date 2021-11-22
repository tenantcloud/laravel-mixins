<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

class QueueOptions
{
	/** Queue for generate chunk jobs. */
	public ?string $pieceQueue = null;

	/** Queue for chunk jobs. */
	public ?string $chunkQueue = null;

	public ?int $delay;

	public function __construct(?string $pieceQueue, ?string $chunkQueue, $delay = null)
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
