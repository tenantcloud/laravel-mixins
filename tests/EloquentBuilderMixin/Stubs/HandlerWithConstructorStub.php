<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;

class HandlerWithConstructorStub implements QueuedChunkHandler
{
	public Carbon $date;

	public function __construct(Carbon $date)
	{
		$this->date = $date;
	}

	public function handle(Collection $items): void
	{
		// For catch in phpunit test
		throw new Exception('Argument date is :' . $this->date->toDateTimeString());
	}
}
