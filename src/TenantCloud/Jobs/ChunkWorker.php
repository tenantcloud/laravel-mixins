<?php

namespace TenantCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravie\SerializesQuery\Eloquent;

class ChunkWorker implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_SIZE = 15;

	protected array $serializedBuilder;

	protected ChunkParams $params;

	protected array $keyValues;

	public function __construct(array $serializedBuilder, ChunkParams $params, array $keyValues)
	{
		$this->serializedBuilder = $serializedBuilder;
		$this->params = $params;
		$this->keyValues = $keyValues;
	}

	public function handle(): void
	{
		$builder = Eloquent::unserialize($this->serializedBuilder);
		$items = $builder->whereIn($this->params->key, $this->keyValues)->get();

		(new $this->params->handler())->handle($items);
	}
}
