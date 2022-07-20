<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;

class HandlerWithConstructorStub implements QueuedChunkHandler
{
	public static ?self $calledThis = null;

	public Carbon $date;

	public function __construct(Carbon $date)
	{
		$this->date = $date;
	}

	public function handle(Collection $items): void
	{
		self::$calledThis = $this;
	}
}
