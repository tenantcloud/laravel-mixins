<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

class SimpleJob implements ShouldQueue
{
	use Queueable;

	public Model $item;

	public string  $name;

	public Carbon $updatedTime;

	public function __construct(Model $item, string $name, Carbon $updatedTime)
	{
		$this->item = $item;
		$this->name = $name;
		$this->updatedTime = $updatedTime;
	}

	public function handle(): void
	{
		$this->item->name = $this->name;
		$this->item->updated_at = $this->updatedTime;
		$this->item->save();
	}
}
