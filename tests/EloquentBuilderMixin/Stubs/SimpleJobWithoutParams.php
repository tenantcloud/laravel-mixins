<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

class SimpleJobWithoutParams implements ShouldQueue
{
	public Model $item;

	public function __construct(Model $item)
	{
		$this->item = $item;
	}

	public function handle(): void
	{
		$this->item->name = 'Job working';
		$this->item->save();
	}
}
