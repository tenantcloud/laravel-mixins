<?php

namespace TenantCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ChunkGenerator implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_PIECE = 1000;

	protected SerializableBuilder $serializedBuilder;

	protected ChunkParams $params;

	protected int $chunkNumber;

	public function __construct(SerializableBuilder $serializedBuilder, ChunkParams $params, int $chunkNumber)
	{
		$this->serializedBuilder = $serializedBuilder;
		$this->params = $params;
		$this->chunkNumber = $chunkNumber;
	}

	public function handle(): void
	{
		$minKeyValue = ($this->chunkNumber - 1) * $this->params->pieceSize + 1;
		$maxKeyValue = $this->chunkNumber * $this->params->pieceSize;

		$builder = $this->serializedBuilder->getBuilder();

		$builder
			->whereBetween($this->params->key, [$minKeyValue, $maxKeyValue])
			->chunkById($this->params->chunkSize, function ($items) {
				/* @var Collection $items */
				dispatch(
					new ChunkWorker(
						$this->serializedBuilder,
						$this->params->key,
						$items->pluck($this->params->key)->toArray(),
						$this->params->handler
					)
				);
			});
	}
}
