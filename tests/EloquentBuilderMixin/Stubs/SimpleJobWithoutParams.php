<?php

namespace Tests\EloquentBuilderMixin\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\Database\Models\TestStub;

class SimpleJobWithoutParams implements ShouldQueue
{
	public function __construct(
		public readonly TestStub $item
	) {}

	public function handle(): void
	{
		$this->item->name = 'Job working';
		$this->item->save();
	}
}
