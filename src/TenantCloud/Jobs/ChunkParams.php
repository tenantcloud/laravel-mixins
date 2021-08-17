<?php

namespace TenantCloud\Jobs;

class ChunkParams
{
	public string $handler;

	public string $key;

	public int $chunkNumber;

	public int $chunkSize;

	public int $pieceSize;

	public function __construct(
		string $handler,
		string $key,
		int $chunkSize,
		int $pieceSize
	) {
		$this->handler = $handler;
		$this->key = $key;
		$this->chunkSize = $chunkSize;
		$this->pieceSize = $pieceSize;
	}
}
