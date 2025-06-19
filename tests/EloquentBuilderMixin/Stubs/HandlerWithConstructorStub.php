<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use Tests\Database\Models\TestStub;

/**
 * @template-implements QueuedChunkHandler<TestStub>
 */
class HandlerWithConstructorStub implements QueuedChunkHandler
{
	public static ?self $calledThis = null;

	public function __construct(public Carbon $date) {}

	public function handle(Collection $items): void
	{
		self::$calledThis = $this;
	}
}
