<?php

namespace TenantCloud\Mixins\Jobs;

class ChunkParams
{
	public string $handler;

	public array $handlerParameters;

	public string $key;

	public string $keyAttributeName;

	public int $chunkNumber;

	public int $chunkSize;

	public int $pieceSize;

	public ?string $queue;

	public function __construct(
		string $handler,
		array $handlerParameters,
		string $key,
		string $keyAttributeName,
		int $chunkSize,
		int $pieceSize,
		?string $queue = null
	) {
		$this->handler = $handler;
		$this->handlerParameters = $handlerParameters;
		$this->key = $key;
		$this->keyAttributeName = $keyAttributeName;
		$this->chunkSize = $chunkSize;
		$this->pieceSize = $pieceSize;
		$this->queue = $queue;
	}
}
