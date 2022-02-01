<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedItemHandler;

class SimpleJob implements ShouldQueue, QueuedItemHandler
{
	use Queueable;

	public Model $item;

	public string  $name;

	public Carbon $updatedTime;

	public function __construct(string $name, Carbon $updatedTime)
	{
		$this->name = $name;
		$this->updatedTime = $updatedTime;
	}

	public function setItem(Model $item): QueuedItemHandler
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
