<?php

namespace TenantCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ChunkWorker implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public const CHUNK_SIZE = 15;

	protected Collection $items;

	protected string $handler;

	protected array $keyValues;

	public function __construct(Collection $items, string $handler)
	{
		$this->items = $items;
		$this->handler = $handler;
	}

	public function handle(): void
	{
		(new $this->handler())->handle($this->items);
	}
}
