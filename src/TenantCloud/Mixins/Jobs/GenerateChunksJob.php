<?php

namespace TenantCloud\Mixins\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Tests\EloquentBuilderMixin\Jobs\GenerateChunksJobTest;

/**
 * @see GenerateChunksJobTest
 */
class GenerateChunksJob implements ShouldQueue
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
					new HandleChunkJob(
						$this->serializedBuilder,
						$this->params->key,
						$items->pluck($this->params->keyAttributeName)->toArray(),
						$this->params->handler,
						$this->params->handlerParameters
					)
				)->onQueue($this->params->queue);
			}, $this->params->key, $this->params->keyAttributeName);
	}
}
