<?php

namespace TenantCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChunkGenerator implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_PIECE = 1000;

	protected string $jobClass;

	protected int $chunkNumber;

	protected int $pieceSize = self::CHUNK_PIECE;

	public function __construct(string $jobClass, int $chunkNumber, int $pieceSize = self::CHUNK_PIECE)
	{
		$this->jobClass = $jobClass;
		$this->chunkNumber = $chunkNumber;
		$this->pieceSize = $pieceSize;
	}

	public function handle(): void
	{
		$minKeyValue = ($this->chunkNumber - 1) * $this->pieceSize;
		$maxKeyValue = $this->chunkNumber * $this->pieceSize - 1;

		/* @var ChunkWorkerContract $job */
		$job = new $this->jobClass($minKeyValue, $maxKeyValue);

		dispatch($job);
	}
}
