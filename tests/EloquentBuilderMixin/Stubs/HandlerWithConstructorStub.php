<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Exception;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Jobs\QueuedChunkHandler;

class HandlerWithConstructorStub implements QueuedChunkHandler
{
	public string $date;

	public function __construct(string $date)
	{
		$this->date = $date;
	}

	public function handle(Collection $items): void
	{
		// For catch in phpunit test
		throw new Exception('Argument date is :' . $this->date);
	}
}
