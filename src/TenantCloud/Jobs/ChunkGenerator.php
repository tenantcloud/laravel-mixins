<?php

namespace TenantCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravie\SerializesQuery\Eloquent;

class ChunkGenerator implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_PIECE = 1000;

	protected array $serializedBuilder;

	protected ChunkParams $params;

	protected int $chunkNumber;

	public function __construct(array $serializedBuilder, ChunkParams $params, int $chunkNumber)
	{
		$this->serializedBuilder = $serializedBuilder;
		$this->params = $params;
		$this->chunkNumber = $chunkNumber;
	}

	public function handle(): void
	{
		$minKeyValue = ($this->chunkNumber - 1) * $this->params->pieceSize;
		$maxKeyValue = $this->chunkNumber * $this->params->pieceSize - 1;

		$builder = Eloquent::unserialize($this->serializedBuilder);

		$builder
			->whereBetween($this->params->key, [$minKeyValue, $maxKeyValue])
			->chunkById($this->params->chunkSize, function ($items) {
				/* @var Collection $items */
				dispatch(new ChunkWorker($items, $this->params->handler));
			});
	}
}
