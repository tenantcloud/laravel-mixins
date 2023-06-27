<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;
use Tests\Database\Models\TestStub;

/**
 * @implements QueuedItemHandler<TestStub>
 */
class SimpleJob implements ShouldQueue, QueuedItemHandler
{
	use Queueable;

	public TestStub $item;

	public function __construct(
		public readonly string $name,
		public readonly Carbon $updatedTime
	) {}

	public function setItem(Model $item): static
	{
		$this->item = $item;

		return $this;
	}

	public function handle(): void
	{
		$this->item->name = $this->name;
		$this->item->updated_at = $this->updatedTime;
		$this->item->save();
	}
}
