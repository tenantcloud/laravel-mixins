<?php

namespace TenantCloud\Mixins\Jobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Laravie\SerializesQuery\Eloquent;

class SerializableBuilder
{
	private string $key;

	private Builder $builder;

	public function __construct(Builder $builder)
	{
		$this->key = uniqid('chunk_job:', false);
		$this->builder = $builder;
	}

	public function __serialize(): array
	{
		Cache::put($this->key, Eloquent::serialize($this->builder), Carbon::now()->addWeek());

		return ['key' => $this->key];
	}

	public function __unserialize(array $data): void
	{
		$this->key = $data['key'];

		$this->builder = Eloquent::unserialize(Cache::get($this->key));
	}

	public function getBuilder(): Builder
	{
		return $this->builder;
	}
}
