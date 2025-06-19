<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use TenantCloud\Mixins\Jobs\GenerateChunksJob;

class QueueOptions
{
	public function __construct(
		/** Queue for generate chunk jobs. */
		public ?string $pieceQueue,
		/** Queue for chunk jobs. */
		public ?string $chunkQueue,
		/** The number of seconds between @see GenerateChunksJob handle */
		public ?int $delay = null
	) {}

	public static function defaultInstance(): self
	{
		return new self(null, null);
	}
}
