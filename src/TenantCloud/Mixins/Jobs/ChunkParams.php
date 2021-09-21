<?php

namespace TenantCloud\Mixins\Jobs;

class ChunkParams
{
	public string $handler;

	public string $key;

	public int $chunkNumber;

	public int $chunkSize;

	public int $pieceSize;

	public ?string $queue;

	public function __construct(
		string $handler,
		string $key,
		int $chunkSize,
		int $pieceSize,
		?string $queue = null
	) {
		$this->handler = $handler;
		$this->key = $key;
		$this->chunkSize = $chunkSize;
		$this->pieceSize = $pieceSize;
		$this->queue = $queue;
	}
}
